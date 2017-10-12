<?php

use Slim\Http\Request;
use Slim\Http\Response;

$f = \Faker\Factory::create('en_GB');

/*
 * Simple stub for an imaginary 'api' request
 */
$app->get('/user/name', function (Request $request, Response $response, array $args) use ($f) {
    return $this->response->withJson([
        'response' => ['name' => $f->name()],
        'token' => (string)$this->jwt
    ]);
});

/*
 * Simple stub for an imaginary 'api' request
 */
$app->get('/user/email', function (Request $request, Response $response, array $args) use ($f) {
    return $this->response->withJson([
        'response' => ['email' => $f->email()],
        'token' => (string)$this->jwt
    ]);
});

/*
 * Simple stub for an imaginary 'api' request
 */
$app->get('/user', function (Request $request, Response $response, array $args) use ($f) {
    return $this->response->withJson([
        'response' => [
            'name' => $f->name(),
            'email' => $f->email(),
            'address' => $f->address
        ],
        'token' => (string)$this->jwt
    ]);
});
