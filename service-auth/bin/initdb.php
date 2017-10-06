<?php

require __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/settings.php';
$dbPath = $config['settings']['database']['path'];

if (file_exists($dbPath)) {
    unlink($dbPath);
}

$db = new \SQLite3($dbPath);

$db->exec('
    CREATE TABLE users
    (
        id INTEGER PRIMARY KEY,
        email TEXT NOT NULL,
        password TEXT NOT NULL,
        firstname TEXT NOT NULL,
        surname TEXT NOT NULL
    )
');
$db->exec('CREATE INDEX email_tindex ON users (email)');

$db->exec('
    CREATE TABLE sso
    (
        id INTEGER PRIMARY KEY,
        user_id INTEGER NOT NULL,
        entity_id TEXT NOT NULL,
        sso TEXT NOT NULL,
        slo TEXT NOT NULL,
        cert TEXT NOT NULL
    )
');
$db->exec('CREATE INDEX user_id_tindex ON sso (user_id)');

// yes, these will all be the same, but it makes testing the login easier!
$password = password_hash('password', PASSWORD_DEFAULT);
$sso = [
    'entity_id' => 'https://app.onelogin.com/saml/metadata/709016',
    'sso' => 'https://andy-dev.onelogin.com/trust/saml2/http-post/sso/709016',
    'slo' => 'https://andy-dev.onelogin.com/trust/saml2/http-redirect/slo/709016',
    'cert'=> 'MIIEGjCCAwKgAwIBAgIURIHjkudifOl64VEQdjxNZIIJZ/wwDQYJKoZIhvcNAQEFBQAwWTELMAkGA1UEBhMCVVMxETAPBgNVBAoMCEVsdWNpZGF0MRUwEwYDVQQLDAxPbmVMb2dpbiBJZFAxIDAeBgNVBAMMF09uZUxvZ2luIEFjY291bnQgMTE1MTQxMB4XDTE3MTAwMTE2MTcwMVoXDTIyMTAwMjE2MTcwMVowWTELMAkGA1UEBhMCVVMxETAPBgNVBAoMCEVsdWNpZGF0MRUwEwYDVQQLDAxPbmVMb2dpbiBJZFAxIDAeBgNVBAMMF09uZUxvZ2luIEFjY291bnQgMTE1MTQxMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3X5034MkM9wXPgHYr7SEJCgCml6VCPXOISyPg3fVF8qU936o8uYvIRJdCfoHA7j1kQWD/kcueMMJscg8+98xh7RvgYPORzHkSx6J3Ke+fEcPZjZCP8n+EK8zeubPn52pUDYTXLSHNYha/GU1UpHjH+tBgE7MVqp4e03vZ1bNIsprPuCQ8+43JyK7pygCoQQXT+Gd5XMMioDKow7lRreE9rPewkI8drsLX2IMIf3raw+EgC6yXyCGb+sxhFdlV8JSF/EuZnsqikvirNj092ltc3ntcS55oBjVT+mIvgnlo5cDC/EsIWBmyqV39WlVQCbWql2sY/wXGVG6BVQ9JUTsVwIDAQABo4HZMIHWMAwGA1UdEwEB/wQCMAAwHQYDVR0OBBYEFDxak7XP2QyywSh4frhVNGEb6Sb6MIGWBgNVHSMEgY4wgYuAFDxak7XP2QyywSh4frhVNGEb6Sb6oV2kWzBZMQswCQYDVQQGEwJVUzERMA8GA1UECgwIRWx1Y2lkYXQxFTATBgNVBAsMDE9uZUxvZ2luIElkUDEgMB4GA1UEAwwXT25lTG9naW4gQWNjb3VudCAxMTUxNDGCFESB45LnYnzpeuFREHY8TWSCCWf8MA4GA1UdDwEB/wQEAwIHgDANBgkqhkiG9w0BAQUFAAOCAQEAcqbeu4YHouOhILe0W33PIIjA8ILs4S+XOCf+DJjUueiWBwEZyu0UyUijclTDNnGKbZdFT+xwuQEXnonvbGmxWvdMFrV9Tj0LxKKuwiXNEB17mf37GEovGGmgOOQ3otzvagJWro6Hci6jL5wm/OdbYyOy/rdLiVJXvUwatYE/kVKONG+2yExKggd8zsA9wMhEsZsAZ+8O4UAXzlZETmmc6QvpvwS0W+Q4b3oS3yWZTIcwqiXF2IJGhuaPOmzb8R13h0Bg1Rj7Ho1EQ9KQ2SWKdRF7lAwPwrlifz+KyYxyiZfcXYlJUpV+RZARnTdEvkwsWps0jArvk9Uhi5nhJBZ6Fg=='
];

$f = \Faker\Factory::create('en_GB');

$db->exec(sprintf("
    INSERT INTO users (`email`, `password`, `firstname`, `surname`) 
    VALUES ('andrew.collington@elucidat.com', '%s', 'Andrew', 'Collington')",
    $db->escapeString($password)
));
$db->exec(sprintf("
    INSERT INTO sso (`user_id`, `entity_id`, `sso`, `slo`, `cert`) 
    VALUES (1, '%s', '%s', '%s', '%s')",
    ...array_values(array_map([$db, 'escapeString'], $sso))
));

for ($i = 2; $i < 12; $i++) {
    $db->exec(sprintf("
        INSERT INTO users (`email`, `password`, `firstname`, `surname`) 
        VALUES ('%s', '%s', '%s', '%s')",
        $db->escapeString($f->safeEmail()),
        $db->escapeString($password),
        $db->escapeString($f->firstName()),
        $db->escapeString($f->lastName())
    ));
    if (!(rand(0, 100)%3)) {
        $db->exec(sprintf("
            INSERT INTO sso (`user_id`, `entity_id`, `sso`, `slo`, `cert`) 
            VALUES (%d, '%s', '%s', '%s', '%s')",
            $i, ...array_values(array_map([$db, 'escapeString'], $sso))
        ));
    }
}
