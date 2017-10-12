<?php

/*
 * Handle CORS requests gracefully, returning a standard error object (json) if
 * anything goes wrong.  This happens for all requests.
 */
$app->add(new \Tuupola\Middleware\Cors([
    'origin' => ['*'],
    'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
    'headers.expose' => ['Etag'],
    'error' => function ($request, $response, $arguments) {
        $data['status'] = 'error';
        $data['message'] = $arguments['message'];
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

/*
 * Handle any request to the application and make sure that it has a JWT available.
 * If it does the user is authenticated and if not they need to login.
 *
 * This middleware will check the cookie, authorization header and more, to see
 * where the token is available.
 */
$app->add(new \Slim\Middleware\JwtAuthentication([
    'path'        => '/',
    'secret'      => file_get_contents(__DIR__.'/../../keys/hs512'),
    'algorithm'   => 'HS512',
    'relaxed'     => ['localhost'],
    'callback'    => function($request, $response, $arguments) use ($container) {
        $container['jwt'] = $arguments['decoded'];
    },
    'error'       => function($request, $response, $arguments) use ($container)  {
        return $response->withStatus(401)->withJson(['error' => 'You are not authenticated']);
    }
]));
