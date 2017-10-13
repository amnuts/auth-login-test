## Getting started

#### Generate the JWT encryption key

By default, this test app is set to use HS512, so at the very least you must generate that key.  For information on how, read the file `keys/hs512.example`.

If you wanted to use a different encryption technique, create a file with that algorithm (eg, hs256, rsa512, etc.)

#### Install dependencies

Run `composer install` in the `site` directory, the `service-auth` directory, and the `service-api` directory.

#### Run the init script to set up the database

To create the example database:

```
cd service-auth
composer run-script initdb
```

The database structure is very simple; you have a users table that holds the authentication details, a saml_entities table that holds information about the IdPs and then a users_saml table that links the two.

The db script will randomly populate the tables with users and assignments to the IdP information.  The IdP is set up with an example OneLogin service.  If you want to use you own service then change the details in this initdb script or just alter the database after it's been created.

#### Run the web servers

The app is set up to use three specific ports on localhost.  The easiest way to get them fired up is to use PHP's built in web server with the following commands:

```
php -S localhost:8010 -t site/public
php -S localhost:8020 -t service-auth/public
php -S localhost:8030 -t service-api/public
```
