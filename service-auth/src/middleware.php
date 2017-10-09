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
