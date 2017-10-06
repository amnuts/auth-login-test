<?php

use Slim\Http\Request;
use Slim\Http\Response;

use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Builder;

// Routes

$app->post('/authenticate', function (Request $request, Response $response, array $args) {
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

    $token = (new Builder())
        ->setAudience('http://localhost:8010')
        ->setIssuer('http://localhost:8020')
        ->setId(uniqid())
        ->setIssuedAt((new \DateTime('now'))->getTimestamp())
        ->setNotBefore((new \DateTime('now'))->getTimestamp())
        ->set('user', (object)[
            'id' => $user['id'],
            'email' => $user['email'],
            'firstname' => $user['firstname'],
            'surname' => $user['surname']
        ])
        ->sign(new Sha512(), new Key(file_get_contents(__DIR__.'/../../keys/hs512')))
        ->getToken();

    return $response->withJson(['token' => (string)$token]);
});
