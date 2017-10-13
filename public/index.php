<?php

require '../app/init.php';

// route: default
$app->get('/', 'GLPI\Telemetry\Controllers\Telemetry:view');

// contact
$app->get('/contact', 'GLPI\Telemetry\Controllers\Contact:view')
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf']);
$app->post('/contact', 'GLPI\Telemetry\Controllers\Contact:send')
   ->add($recaptcha)
   ->add($container['csrf']);

// reference
$app->get('/reference', 'GLPI\Telemetry\Controllers\Reference:view')
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf']);
$app->post('/reference', 'GLPI\Telemetry\Controllers\Reference:register')
   ->add($recaptcha)
   ->add($container['csrf']);

// telemetry
$app->get('/telemetry', 'GLPI\Telemetry\Controllers\Telemetry:view');
$app->post('/telemetry', 'GLPI\Telemetry\Controllers\Telemetry:send')
   ->add(new \GLPI\Telemetry\Middleware\JsonCheck($container));
$app->get('/telemetry/geojson', 'GLPI\Telemetry\Controllers\Telemetry:geojson');

$app->get('/telemetry/schema.json', 'GLPI\Telemetry\Controllers\Telemetry:schema');

// special pages
$app->get('/ok', function ($request, $response, $args) {
    return $this->view->render($response, 'ok.html', [
      'class' => 'ok'
    ]);
});

// run slim
$app->run();
