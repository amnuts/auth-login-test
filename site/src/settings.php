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

        // SSO
        'sso' => [
            'sp' => [
                'entityId' => '/auth/sso/metadata',
                'assertionConsumerService' => ['url' => '/auth/sso/acs'],
                'singleLogoutService' => ['url' => '/auth/sso/sls'],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
            ],
            'idp' => [
                'entityId' => 'https://app.onelogin.com/saml/metadata/709016',
                'singleSignOnService' => [
                    'url' => 'https://andy-dev.onelogin.com/trust/saml2/http-post/sso/709016',
                ],
                'singleLogoutService' => [
                    'url' => 'https://andy-dev.onelogin.com/trust/saml2/http-redirect/slo/709016',
                ],
                'x509cert' => 'MIIEGjCCAwKgAwIBAgIURIHjkudifOl64VEQdjxNZIIJZ/wwDQYJKoZIhvcNAQEF
BQAwWTELMAkGA1UEBhMCVVMxETAPBgNVBAoMCEVsdWNpZGF0MRUwEwYDVQQLDAxP
bmVMb2dpbiBJZFAxIDAeBgNVBAMMF09uZUxvZ2luIEFjY291bnQgMTE1MTQxMB4X
DTE3MTAwMTE2MTcwMVoXDTIyMTAwMjE2MTcwMVowWTELMAkGA1UEBhMCVVMxETAP
BgNVBAoMCEVsdWNpZGF0MRUwEwYDVQQLDAxPbmVMb2dpbiBJZFAxIDAeBgNVBAMM
F09uZUxvZ2luIEFjY291bnQgMTE1MTQxMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
MIIBCgKCAQEA3X5034MkM9wXPgHYr7SEJCgCml6VCPXOISyPg3fVF8qU936o8uYv
IRJdCfoHA7j1kQWD/kcueMMJscg8+98xh7RvgYPORzHkSx6J3Ke+fEcPZjZCP8n+
EK8zeubPn52pUDYTXLSHNYha/GU1UpHjH+tBgE7MVqp4e03vZ1bNIsprPuCQ8+43
JyK7pygCoQQXT+Gd5XMMioDKow7lRreE9rPewkI8drsLX2IMIf3raw+EgC6yXyCG
b+sxhFdlV8JSF/EuZnsqikvirNj092ltc3ntcS55oBjVT+mIvgnlo5cDC/EsIWBm
yqV39WlVQCbWql2sY/wXGVG6BVQ9JUTsVwIDAQABo4HZMIHWMAwGA1UdEwEB/wQC
MAAwHQYDVR0OBBYEFDxak7XP2QyywSh4frhVNGEb6Sb6MIGWBgNVHSMEgY4wgYuA
FDxak7XP2QyywSh4frhVNGEb6Sb6oV2kWzBZMQswCQYDVQQGEwJVUzERMA8GA1UE
CgwIRWx1Y2lkYXQxFTATBgNVBAsMDE9uZUxvZ2luIElkUDEgMB4GA1UEAwwXT25l
TG9naW4gQWNjb3VudCAxMTUxNDGCFESB45LnYnzpeuFREHY8TWSCCWf8MA4GA1Ud
DwEB/wQEAwIHgDANBgkqhkiG9w0BAQUFAAOCAQEAcqbeu4YHouOhILe0W33PIIjA
8ILs4S+XOCf+DJjUueiWBwEZyu0UyUijclTDNnGKbZdFT+xwuQEXnonvbGmxWvdM
FrV9Tj0LxKKuwiXNEB17mf37GEovGGmgOOQ3otzvagJWro6Hci6jL5wm/OdbYyOy
/rdLiVJXvUwatYE/kVKONG+2yExKggd8zsA9wMhEsZsAZ+8O4UAXzlZETmmc6Qvp
vwS0W+Q4b3oS3yWZTIcwqiXF2IJGhuaPOmzb8R13h0Bg1Rj7Ho1EQ9KQ2SWKdRF7
lAwPwrlifz+KyYxyiZfcXYlJUpV+RZARnTdEvkwsWps0jArvk9Uhi5nhJBZ6Fg==
',
            ],
        ]
    ],
];
