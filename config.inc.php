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
   'recaptcha' => [
      'sitekey'   => '6Ld2NiYUAAAAANz_4QICzOtcG3GKKBQBn8hWa-Oc',
      'secret'    => '6Ld2NiYUAAAAADx83gtAuZxXphOr2RXF1-9aH56Y'
   ],
   'mail_admin'             => 'adelaunay@teclib.com',
   'debug'                  => true,

   // slim configuration
   'displayErrorDetails'    => true,
   'addContentLengthHeader' => false,
];
