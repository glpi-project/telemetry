<?php

return $config = [
   'db' => [
      'driver'    => "pgsql",
      'host'      => "localhost",
      'database'  => "glpi_telemetry",
      'username'  => "adelaunay",
      'password'  => "adelaunay",
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix'    => '',
   ],
   'debug' => true,
   'displayErrorDetails' => true,
   'addContentLengthHeader' => false,
];
