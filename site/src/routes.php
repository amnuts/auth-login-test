<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// Obviously having all of this split into controllers would be far better!

$app->get('/projects', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/projects' route");
    return $this->renderer->render($response, 'projects.phtml', $args);
});

$app->get('/account', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/account' route");
    return $this->renderer->render($response, 'account.phtml', $args);
});

$app->get('/login', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/login' route");
    $data = [
        'messages' => $this->flash->getMessages()
    ];
    return $this->renderer->render($response, 'login.phtml', $data);
});

$app->get('/logout', function (Request $request, Response $response, array $args) {
    unset($_COOKIE['token']);
    setcookie('token', null, -1, '', 'localhost');
    $this->flash->addMessage('info', 'You have been logged out');
    return $response->withRedirect('/login');
});

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
    setcookie('token', $result->token, 0, '/', 'localhost');
    return $response->withRedirect('/projects');
});

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

$app->get('/info', function (Request $request, Response $response, array $args) {
    return phpinfo();
});

$app->get('/', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});

