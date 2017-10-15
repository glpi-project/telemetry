<?php

require '../app/init.php';

// route: default
$app->get('/', 'GLPI\Telemetry\Controllers\Telemetry:view')
    ->setName('telemetry');

if ($container->project->hasContactPage()) {
    // contact
    $app->get('/contact', 'GLPI\Telemetry\Controllers\Contact:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('contact');
    $app->post('/contact', 'GLPI\Telemetry\Controllers\Contact:send')
        ->add($recaptcha)
        ->add($container['csrf'])
        ->setName('sendContact');
}
// reference
$app->get('/reference', 'GLPI\Telemetry\Controllers\Reference:view')
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('reference');
$app->post('/reference', 'GLPI\Telemetry\Controllers\Reference:register')
   ->add($recaptcha)
   ->add($container['csrf'])
   ->setName('registerReference');

// telemetry
$app->get('/telemetry', 'GLPI\Telemetry\Controllers\Telemetry:view');
$app->post('/telemetry', 'GLPI\Telemetry\Controllers\Telemetry:send')
   ->add(new \GLPI\Telemetry\Middleware\JsonCheck($container));
$app->get('/telemetry/geojson', 'GLPI\Telemetry\Controllers\Telemetry:geojson')
    ->setName('geojson');

$app->get('/telemetry/schema.json', 'GLPI\Telemetry\Controllers\Telemetry:schema')
    ->setName('schema');

$app->get(
    '/logo.png',
    function ($request, $response) {
        echo $this->project->getLogo();
    }
)->setName('logo');

// run slim
$app->run();
