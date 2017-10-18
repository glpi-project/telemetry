<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Base installation script
 *
 * PHP version 7
 *
 * @category Migration
 * @package  Telemetry
 * @author   Johan Cwiklinski <johan@x-tnd.be>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://githu.com/glpi-project/telemetry
 */

if (!file_exists(__DIR__ . '/config.inc.php')) {
    throw new \RuntimeException('Configuration file is missing!');
}
require_once __DIR__ . '/config.inc.php';

$dconf = $config['db'];
$pconfig = [
    'paths'         => [
        'migrations'    => ['%%PHINX_CONFIG_DIR%%/db/migrations'],
        'seeds'         => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments'  => [
        'default_migration_table'   => 'phinxlog',
        'default_database'          => 'development',
        'development'               => [
            'name'          => $dconf['database'],
            'adapter'       => $dconf['driver'],
            'host'          => $dconf['host'],
            'user'          => $dconf['username'],
            'pass'          => $dconf['password'],
            'table_prefix'  => $dconf['prefix'],
            'charset'       => $dconf['charset'],
            'collation'     => $dconf['collation']
        ]
    ]
];

if (file_exists(__DIR__ . '/phinx_local.php')) {
    //permit to override $pconfig
    //for example to change seeds path
    require_once __DIR__ . '/phinx_local.php';
}

return $pconfig;
