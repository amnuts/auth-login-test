<?php

$container = $app->getContainer();

/*
 * Simple PHP view renderer
 */
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

/*
 * Include monolog - don't really need to do this, but handy for testing
 */
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

/*
 * Flash messenger - handy for feedback on posted forms.
 */
$container['flash'] = function ($c) {
    return new Slim\Flash\Messages();
};

/*
 * Token generation
 *
 * Set up as a function in a function so that the DI container can be used to
 * construct this object with different parameters.
 */
$container['token'] = function($c) {
    return function ($token) use ($c) {
        return new \Components\Token($c, $token, $alg = null);
    };
};
