<?php

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

/*
 * Handle any request to the application and make sure that it has a JWT available.
 * If it does the user is authenticated and if not they need to login.
 *
 * This middleware will check the cookie, authorization header and more, to see
 * where the token is available.
 */
$app->add(new \Slim\Middleware\JwtAuthentication([
    'path'        => '/',
    'passthrough' => ['/login', '/sso/login', '/info'],
    'secret'      => file_get_contents(__DIR__.'/../../keys/hs512'),
    'algorithm'   => 'HS512',
    'relaxed'     => ['localhost'],
    /*
     * This callback is run anytime the JWT is verified.  This would be used
     * for additional validation, such as the expiration time, nonce (jti),
     * nbf, iss, etc.
     */
    'callback'    => function($request, $response, $arguments) use ($container) {
        /*
         * This puts it in the di container for use in routers with $this->jwt->...
         */
        $container['jwt'] = $arguments['decoded'];
    },
    /*
     * If something goes wrong, fire off this callback.
     */
    'error'       => function($request, $response, $arguments) use ($container)  {
        return $response->withRedirect('/login');
    }
]));
