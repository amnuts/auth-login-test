<?php

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

//$logger = new Logger("slim");
//$rotating = new RotatingFileHandler(__DIR__ . "/../logs/jwt.log", 0, Logger::DEBUG);
//$logger->pushHandler($rotating);

$app->add(new \Slim\Middleware\JwtAuthentication([
    'path'        => '/projects',
    'passthrough' => ['/login', '/sso/login', '/info'],
    'secret'      => file_get_contents(__DIR__.'/../../keys/hs512'),
    'algorithm'   => 'HS512',
    'relaxed'     => ['localhost'],
    //'logger'      => $logger,
    'callback'    => function($request, $response, $arguments) use ($container) {
        // could validate exp, iat, etc. here, returning false if not satisfied.
        // this puts it in the di container for use in routers with $this->jwt->...
        $container['jwt'] = $arguments['decoded'];
    },
    'error'       => function($request, $response, $arguments) use ($container)  {
        return $response->withRedirect('/login');
    }
]));
