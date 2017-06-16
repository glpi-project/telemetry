<?php

require '../app/init.php';

// route: default
$app->get('/',          'App\Controllers\Telemetry:view');
$app->get('/telemetry', 'App\Controllers\Telemetry:view');
$app->get('/contact',   'App\Controllers\Contact:view');
$app->get('/reference', 'App\Controllers\Reference:view');

// route: post json file
$app->post('/telemetry', 'App\Controllers\Telemetry:send');

// run slim
$app->run();
