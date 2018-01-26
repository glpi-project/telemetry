How to install Telemetry
========================

Clone the project, install [composer](https://getcomposer.org) and run `composer install` in the project root.

Create database
---------------

The project is designed to use PostgreSQL; this is the only database engine supported and tested.

Create an user and a database for telemetry:

```
# su - postgres
$ createuser -P username
$ createdb -O username databasename
```

Setup
-----

Copy the `config.inc.php.dist` to `config.inc.php` and adapt it to fit your installation.

Once done, use [phinx](https://phinx.org/) to create the database:

```
$ ./vendor/bin/phinx migrate
```

This will create tables in the database.

You also can define your own migrations script by adding your path in a `phinx_local.php`:

```php
<?php

$pconfig['paths']['migrations'][] = '%%PHINX_CONFIG_DIR%%/projects/db/my_migrations';
```

Example dataset
---------------

You can also import a default dataset with phinx seeds (this is not mandatory):

```
$ ./vendor/bin/phinx seed:run
```

Here again, you can define your own seeds by adding your path in the `phinx_local.php`:

```php
<?php

$pconfig['paths']['seeds'] = '%%PHINX_CONFIG_DIR%%/projects/db/my_seeds';
```

This permits to use a different seed directory than the default one; it will not be used anymore.
Refer to the [phinx documentation to know how to create seeds](http://docs.phinx.org/en/latest/seeding.html).
