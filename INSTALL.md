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

Copy the `config.inc.php.dist` to `config.inc.php` and adapt it to fit your installation.

Once done, use [phinx](https://phinx.org/) to create the database:

```
$ ./vendor/bin/phinx migrate
```

This will create tables in the database.

You can also import a default dataset with phinx seeds, this is not mandatory:

```
$ ./vendor/bin/phinx seed:run
```

Note that the default configuration will use a provided dataset specific to GLPI. If you want to get your own seeds, create a `phinx_local.php` file with the following content:

```php
<?php

$pconfig['paths']['seeds'] = '%%PHINX_CONFIG_DIR%%/projects/db/my_seeds';
```

This permits to use a different seed directory than the default one. Refer to the phinx documentation to know how to create seeds.

You also can define your own migrations script by adding you path:

```php
<?php

$pconfig['paths']['migrations'][] = '%%PHINX_CONFIG_DIR%%/projects/db/my_migrations';
```
