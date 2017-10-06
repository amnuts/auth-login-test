<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// database
$container['db'] = function($c) {
    $path = $c->get('settings')['database']['path'];
    if (!file_exists($path)) {
        throw new \Exception('You must initialize the database before you start.  Use \'composer run-script initdb\'.');
    }
    return new \SQLite3($path);
};

// Token generation
$container['token'] = function($c) {
    return function ($user) use ($c) {
        return new \Components\Token($c, $user);
    };
};

// SSO settings generation
$container['sso'] = function($c) {
    return function ($idpCode) use ($c) {
        return new \Components\SSO($c, $idpCode);
    };
};
