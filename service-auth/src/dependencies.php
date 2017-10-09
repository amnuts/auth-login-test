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
 * Get the database connection
 */
$container['db'] = function($c) {
    $path = $c->get('settings')['database']['path'];
    if (!file_exists($path)) {
        throw new \Exception('You must initialize the database before you start.  Use \'composer run-script initdb\'.');
    }
    return new \SQLite3($path);
};

/*
 * Token generation
 *
 * Set up as a function in a function so that the DI container can be used to
 * construct this object with different parameters.
 */
$container['token'] = function($c) {
    return function ($user) use ($c) {
        return new \Components\Token($c, $user);
    };
};

/*
 * SSO settings generation
 *
 * Set up as a function in a function so that the DI container can be used to
 * construct this object with different parameters.
 */
$container['sso'] = function($c) {
    return function ($idpCode) use ($c) {
        return new \Components\SSO($c, $idpCode);
    };
};
