## Getting started

Generate at least the hs512 key; see `keys/hs512.example`

Run `composer install` in the repo root, the `site` directory, and the `service-auth` directory.

Run the init script to set up the database:

```
cd service-auth
composer run-script initdb
```

Run the web servers:

```
php -S localhost:8010 -t public site/public/index.php
php -S localhost:8020 -t public service-auth/public/index.php
```
