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

// yes, these will all be the same, but it makes testing the login easier!
$password = password_hash('password', PASSWORD_DEFAULT);
$f = \Faker\Factory::create('en_GB');

$db->exec(sprintf("
    INSERT INTO users (`email`, `password`, `firstname`, `surname`) 
    VALUES ('andrew.collington@elucidat.com', '%s', 'Andrew', 'Collington')",
    $db->escapeString($password)
));

for ($i = 0; $i < 10; $i++) {
    $db->exec(sprintf("
        INSERT INTO users (`email`, `password`, `firstname`, `surname`) 
        VALUES ('%s', '%s', '%s', '%s')",
        $db->escapeString($f->safeEmail()),
        $db->escapeString($password),
        $db->escapeString($f->firstName()),
        $db->escapeString($f->lastName())
    ));
}
