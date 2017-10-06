<?php

$app->add(new \Slim\Middleware\JwtAuthentication([
    'path'        => '/',
    'passthrough' => ['/login', '/info'],
    'secret'      => file_get_contents(__DIR__.'/../../keys/hs512'),
    'algorithm'   => 'HS512',
    'relaxed'     => ['localhost'],
    'callback'    => function($request, $response, $arguments) use ($container) {
        // could validate exp, iat, etc. here, returning false if not satisfied.
        // this puts it in the di container for use in routers with $this->jwt->...
        $container['jwt'] = $arguments['decoded'];
    },
    'error'       => function($request, $response, $arguments) {
        return $response->withRedirect('/login');
    }
]));
