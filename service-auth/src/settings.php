<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // database
        'database' => [
            'path' => __DIR__.'/../users.sqlite'
        ],

        // SSO logins
        'sso' => [
            'sp' => [
                'entityId' => 'http://localhost:8020/sso/%s/metadata',
                'assertionConsumerService' => ['url' => 'http://localhost:8020/sso/%s/acs',],
                'singleLogoutService' => ['url' => 'http://localhost:8020/sso/%s/slo',],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
            ],
            'idp' => [
                'entityId' => '',
                'singleSignOnService' => ['url' => ''],
                'singleLogoutService' => ['url' => ''],
                'x509cert' => '',
            ]
        ]
    ],
];
