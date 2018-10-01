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

/** References */
//References list
$app->get('/reference[/page/{page:\d+}]', 'GLPI\Telemetry\Controllers\Reference:view')
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('reference');

//References filtering
$app->map(
    ['get', 'post'],
    '/reference/{action:filter|order}[/{value}]',
    'GLPI\Telemetry\Controllers\Reference:filter'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterReferences');

//Reference registration
$app->post('/reference', 'GLPI\Telemetry\Controllers\Reference:register')
   ->add($recaptcha)
   ->add($container['csrf'])
   ->setName('registerReference');
/** /References */

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

$app->get(
    '/json-data-example',
    function ($request, $response) {
        $response->getBody()->write($this->project->getExampleData());
        return $response;
    }
)->setName('jsonExemple');

$app->get(
    '/telemetry/plugins/all',
    'GLPI\Telemetry\Controllers\Telemetry:allPlugins'
)->setName('allPlugins');

$app->post(
    '/references/filter',
    'GLPI\Telemetry\Controllers\References\doFilter'
)->setName('filter_references');

// run slim
$app->run();
