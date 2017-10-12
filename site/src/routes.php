<?php

use Slim\Http\Request;
use Slim\Http\Response;

/*
 * Obviously having all of this split into controllers would be far better!
 */

/*
 * Simple stub for an imaginary 'projects' page
 */
$app->get('/projects', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/projects' route");
    return $this->renderer->render($response, 'projects.phtml', $args);
});

/*
 * Simple stub for an imaginary 'accounts' page
 */
$app->get('/account', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/account' route");
    return $this->renderer->render($response, 'account.phtml', $args);
});

/*
 * The main login page.
 *
 * This is the simple display of the login form.
 */
$app->get('/login', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/login' route");
    $data = ['messages' => $this->flash->getMessages()];
    return $this->renderer->render($response, 'login.phtml', $data);
});

/*
 * The main login age form handler.
 *
 * This is the route called when posting the form and will handle the call to
 * the authentication microservice.
 */
$app->post('/login', function (Request $request, Response $response, array $args) {
    $curl = curl_init('http://localhost:8020/epl');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $request->getParam('email'),
        'password' => $request->getParam('password')
    ]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);

    if ($result) {
        $result = json_decode($result);
        if ($result->error) {
            $this->flash->addMessage('error', $result->error);
            return $response->withRedirect('/login');
        }
    }

    /*
     * All is good so set the cookie for the root of the domain.
     */
    setcookie('token', $result->token, 0, '/', 'localhost');
    return $response->withRedirect('/projects');
});

/*
 * The main logout page.
 *
 * Essentially, if JWT is used throughout the app as the indication of being logged
 * in, then just remove that token regardless of how the person authenticated.
 */
$app->get('/logout', function (Request $request, Response $response, array $args) {
    unset($_COOKIE['token']);
    setcookie('token', null, -1, '', 'localhost');
    $this->flash->addMessage('info', 'You have been logged out');
    return $response->withRedirect('/login');
});


/*
 * Handle setting the JWT cookie from a successful SAML or Social login.
 */
$app->get('/sso/login/{jwt}', function (Request $request, Response $response, array $args) {
    $jwt = base64_decode($args['jwt']);
    $token = $this->get('token')($jwt, 'HS512');
    if (!$token->validate()) {
        return $response->withRedirect('/login');
    }
    echo 'setting cookie';
    setcookie('token', $jwt, 0, '/', 'localhost');
    return $response->withRedirect('/projects');
});

/*
 * Pure testing purposes
 */
$app->get('/ws', function (Request $request, Response $response, array $args) {
    return $this->renderer->render($response, 'webservice.phtml', ['token' => $this->jwt]);
});

/*
 * Pure testing purposes
 */
$app->get('/info', function (Request $request, Response $response, array $args) {
    return phpinfo();
});

/*
 * The homepage - every site's gotta have one!
 */
$app->get('/', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});
