<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

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

$app->post('/login', function (Request $request, Response $response, array $args) {
    $curl = curl_init('http://localhost:8020/authenticate');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $request->getParam('username'),
        'password' => $request->getParam('password')
    ]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        $result = json_decode($response);
        if ($result->error) {
            $this->flash->addMessage('Error', $result->error);
            return $response->withRedirect('/login');
        }
    }

    setcookie('token', $result->token, 0, '', 'localhost');
    return $response->withRedirect('/projects');

    /*
    $context  = stream_context_create([
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'get',
            'content' => http_build_query([
                'username' => $request->getParam('username'),
                'password' => $request->getParam('password')
            ]),
        ]
    ]);
    $result = file_get_contents('http://localhost:8020/authenticate', false, $context);
    if ($result) {
        $result = json_decode($result);
        if ($result->error) {
            $this->flash->addMessage('Error', $result->error);
            return $response->withRedirect('/login');
        }
    }
    */
});

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});
