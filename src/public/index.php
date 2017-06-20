<?php

require '../app/init.php';

// route: default
$app->get('/',          'App\Controllers\Telemetry:view');

$app->get('/contact',   'App\Controllers\Contact:view');
$app->post('/contact',   'App\Controllers\Contact:send')
    ->add($recaptcha);

$app->get('/reference', 'App\Controllers\Reference:view');
$app->post('/reference', 'App\Controllers\Reference:register')
    ->add($recaptcha);


$app->get('/telemetry', 'App\Controllers\Telemetry:view');
$app->post('/telemetry', 'App\Controllers\Telemetry:send');

// run slim
$app->run();
