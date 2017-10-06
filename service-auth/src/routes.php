<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// email/password login
$app->post('/epl', function (Request $request, Response $response, array $args) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');

    if (empty($email) || empty($password)) {
        return $response
            ->withStatus(401)
            ->withJson(['error' => 'Email or password not supplied']);
    }

    $user = $this->db->querySingle("select * from users where email = '" . $this->db->escapeString($email) . "'", true);
    if (empty($user) ||  !password_verify($password, $user['password'])) {
        return $response
            ->withStatus(401)
            ->withJson(['error' => 'Email or password is incorrect']);
    }

    $token = $this->get('token')($user);
    return $response->withJson(['token' => (string)$token->generate()]);
});

$app->post('/sso/{idpCode:[0-9]+}/acs', function (Request $request, Response $response, array $args) {
    $sso = $this->get('sso')($args['idpCode']);
    if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
        $requestID = $_SESSION['AuthNRequestID'];
    } else {
        $requestID = null;
    }

    $auth = new OneLogin_Saml2_Auth($sso->getSettings());
    $auth->processResponse($requestID);

    if (!$auth->isAuthenticated()) {
        // if the user is coming from an IdP, we probably need to display a message rather than
        // send them off to the main site to login.
        // $errors = $auth->getErrors();
        return $response->withHeader('Location', 'http://localhost:8010/login');
    }

    $_SESSION['samlUserdata'] = $auth->getAttributes();
    $_SESSION['samlNameId'] = $auth->getNameId();
    $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
    $_SESSION['samlSessionIndex'] = $auth->getSessionIndex();
    unset($_SESSION['AuthNRequestID']);
    if (isset($_POST['RelayState']) && OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
        $auth->redirectTo($_POST['RelayState']);
    }

    $user = $this->db->querySingle("select * from users where email = '" . $this->db->escapeString($auth->getNameId()) . "'", true);
    $token = $this->get('token')($user);
    // base64 as php internal web server & slim freak out with a '.' in the url
    return $response->withRedirect('http://localhost:8010/sso/login/' . base64_encode((string)$token->generate()));
});

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