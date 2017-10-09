<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handle email and password logins.
 *
 * This is basically the default login format; someone entering their details
 * into form on the website.
 */
$app->post('/epl', function (Request $request, Response $response, array $args) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');

    /*
     * Respond with a standard error object is details not supplied
     */
    if (empty($email) || empty($password)) {
        return $response
            ->withStatus(401)
            ->withJson(['error' => 'Email or password not supplied']);
    }

    /*
     * Respond with a standard error object is details not found or
     * the password is incorrect.
     */
    $user = $this->db->querySingle("select * from users where email = '" . $this->db->escapeString($email) . "'", true);
    if (empty($user) ||  !password_verify($password, $user['password'])) {
        return $response
            ->withStatus(401)
            ->withJson(['error' => 'Email or password is incorrect']);
    }

    /*
     * All good so generate the token and respond back with a simple json object
     * which includes the token string.
     */
    $token = $this->get('token')($user);
    return $response->withJson(['token' => (string)$token->generate()]);
});

/**
 * SP side of the SAML login process, the consumer endpoint.
 */
$app->post('/sso/{idpCode:[0-9]+}/acs', function (Request $request, Response $response, array $args) {
    /*
     * Route parameter used to do idp look-up in db
     */
    $sso = $this->get('sso')($args['idpCode']);
    if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
        $requestID = $_SESSION['AuthNRequestID'];
    } else {
        $requestID = null;
    }

    $auth = new OneLogin_Saml2_Auth($sso->getSettings());
    $auth->processResponse($requestID);

    if (!$auth->isAuthenticated()) {
        /*
         * if the user is coming from an IdP, we probably need to display a message rather than
         * send them off to the main site to login.
         * $errors = $auth->getErrors();
         */
        return $response->withHeader('Location', 'http://localhost:8010/login');
    }

    /*
     * These details could be used to add claims to the JWT
     */
    $_SESSION['samlUserdata'] = $auth->getAttributes();
    $_SESSION['samlNameId'] = $auth->getNameId();
    $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
    $_SESSION['samlSessionIndex'] = $auth->getSessionIndex();
    unset($_SESSION['AuthNRequestID']);
    if (isset($_POST['RelayState']) && OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
        $auth->redirectTo($_POST['RelayState']);
    }

    /*
     * Make sure that this user that just successfully authenticated via the IdP
     * does actually have an account in the system.
     */
    $user = $this->db->querySingle("select * from users where email = '" . $this->db->escapeString($auth->getNameId()) . "'", true);
    if (empty($user)) {
        /*
         * Would probably need to display a message rather than just send them off to the main
         * site to login.  Could be that a flash message is sent?
         */
        return $response->withHeader('Location', 'http://localhost:8010/login');
    }

    /*
     * Generate token and base 64 is again.  This second time is only because
     * the php internal web server & slim freak out with a '.' in the url.
     * Probably wouldn't need to do this on 'real' servers.
     */
    $token = $this->get('token')($user);
    return $response->withRedirect('http://localhost:8010/sso/login/' . base64_encode((string)$token->generate()));
});

/**
 * Allows IdP to get metadata information
 */
$app->get('/sso/{idpCode:[0-9]+}/metadata', function (Request $request, Response $response, array $args) {
    try {
        $sso = $this->get('sso')($args['idpCode']);
        $settings = new OneLogin_Saml2_Settings($sso->getSettings(), true);
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (empty($errors)) {
            return $response->withHeader('Content-type', 'text/xml')->withBody($metadata);
        } else {
            throw new OneLogin_Saml2_Error(
                'Invalid SP metadata: '.implode(', ', $errors),
                OneLogin_Saml2_Error::METADATA_SP_INVALID
            );
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
});

/**
 * Single logout functionality.
 *
 * This doesn't really need to exist.  If the setup was to use JWT as the auth
 * token (which this example is), then just remove that token and the user is
 * effectively no longer logged in.
 */
$app->get('/sso/{idpCode:[0-9]+}/slo', function (Request $request, Response $response, array $args) {
    $nameId = null;
    $sessionIndex = null;
    $nameIdFormat = null;

    $sso = $this->get('sso')($args['idpCode']);
    $auth = new OneLogin_Saml2_Auth($sso->getSettings());
    if (isset($_SESSION['samlNameId'])) {
        $nameId = $_SESSION['samlNameId'];
    }
    if (isset($_SESSION['samlSessionIndex'])) {
        $sessionIndex = $_SESSION['samlSessionIndex'];
    }
    if (isset($_SESSION['samlNameIdFormat'])) {
        $nameIdFormat = $_SESSION['samlNameIdFormat'];
    }

    $auth->logout(null, [], $nameId, $sessionIndex, false, $nameIdFormat);
});

/**
 * Google login.
 *
 * A project needs to be set up on Google first and the details of that stored
 * in the google.php settings file.  This could potentially be opened up to more
 * social login processors than just Google.
 */
$app->any('/oauth/google', function (Request $request, Response $response, array $args) {
    try {
        /*
         * Has the auth process started and we need to handle it or is this
         * the start/end of the process in which case can we set up the token?
         */
        if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
            Hybrid_Endpoint::process();
        } else {
            /*
             * Hybrid Auth 3 changes its API quite a bit from this.
             * Essentially, this is where the other providers would be added,
             * using whatever keys are required for the provider.
             */
            $auth = new \Hybrid_Auth(array(
                'base_url' => 'http://localhost:8020/oauth/google',
                'providers' => array(
                    'Google' => array(
                        'enabled' => true,
                        'keys' => include __DIR__ . '/google.php'
                    )
                )
            ));
            /*
             * This needs to match the provider being used.  So if the user
             * was given the choice of what provider they were going to use
             * then that choice would need to be provided (or have different
             * routes for different auth paths).
             *
             * The information gathered here could be used in the JWT claims.
             */
            $google = $auth->authenticate('Google');
            $accessToken = $google->getAccessToken();
            $userProfile = $google->getUserProfile();

            /*
             * Make sure that this user that just successfully authenticated
             * via the social auth provider does actually have an account
             * in the system.
             */
            $email = (@$userProfile->emailVerified ?: $userProfile->email);
            $user = $this->db->querySingle("select * from users where email = '" . $this->db->escapeString($email) . "'", true);
            if (empty($user)) {
                /*
                 * Unlike SAML, user starts off login process through the
                 * login form by clicking on the 'sign in with...' button,
                 * so just redirect them back there.
                 */
                return $response->withHeader('Location', 'http://localhost:8010/login');
            }

            /*
             * Generate token and base 64 is again.  This second time is only because
             * the php internal web server & slim freak out with a '.' in the url.
             * Probably wouldn't need to do this on 'real' servers.
             */
            $token = $this->get('token')($user);
            return $response->withRedirect('http://localhost:8010/sso/login/' . base64_encode((string)$token->generate()));
        }
    } catch(\Exception $e){
        /*
         * Should probably send a flash message with the exception message.
         */
        return $response->withHeader('Location', 'http://localhost:8010/login');
    }
});