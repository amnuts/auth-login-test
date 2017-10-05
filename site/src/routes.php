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
    return $this->renderer->render($response, 'login.phtml', $args);
});

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    $this->logger->info("'/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});
