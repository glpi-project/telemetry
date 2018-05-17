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


if ($container->project->hasConnectionPage()) {
    // connection
    $app->get('/connection', 'GLPI\Telemetry\Controllers\Connection:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('connection');
    $app->get('/disconnect', 'GLPI\Telemetry\Controllers\Connection:disconnect')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('disconnect');
    $app->post('/connection', 'GLPI\Telemetry\Controllers\Connection:send')
        ->add($container['csrf'])
        ->setName('sendConnection');
}

if ($container->project->hasRegisterPage()) {
    // register
    $app->get('/register', 'GLPI\Telemetry\Controllers\Register:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('register');
    $app->post('/register', 'GLPI\Telemetry\Controllers\Register:send')
        ->add($container['csrf'])
        ->setName('sendRegister');
}

if ($container->project->hasProfilePage()) {
    // profile
    $app->get('/profile[/status/{status}[/page/{page:\d+}]]', 'GLPI\Telemetry\Controllers\Profile:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('profile');

    //Profile update reference
    $app->map(
        ['get', 'post'],
        '/profile/update',
        'GLPI\Telemetry\Controllers\Reference:update'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('actionProfileReferenceUpdate');

    //Profile delete reference
    $app->map(
        ['get', 'post'],
        '/profile/delete',
        'GLPI\Telemetry\Controllers\Reference:delete'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('actionProfileReferenceDelete');

    //Profile sorting
    $app->map(
        ['get', 'post'],
        '/profile/view[/{status}]',
        'GLPI\Telemetry\Controllers\Profile:view'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('sorterProfile');
}

if ($container->project->hasAdminPage()) {
    // admin
    $app->get('/admin[/status/{status}[/page/{page:\d+}]]', 'GLPI\Telemetry\Controllers\Admin:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('admin');

    //Admin denied
    $app->map(
        ['get', 'post'],
        '/admin/ActionReferencePost',
        'GLPI\Telemetry\Controllers\Admin:ActionReferencePost'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('actionAdminReferencesWithMsg');

    //Admin sorting
    $app->map(
        ['get', 'post'],
        '/admin/view[/{status}]',
        'GLPI\Telemetry\Controllers\Admin:view'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('sorterAdmin');

    //Admin References filtering
    $app->map(
        ['get', 'post'],
        '/admin/filter[/order/{orderby}]',
        'GLPI\Telemetry\Controllers\Admin:filter'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('filterAdminReferences');
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
    '/reference/filter[/order/{orderby}]',
    'GLPI\Telemetry\Controllers\Reference:filter'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterReferences');

//Profile References filtering
$app->map(
    ['get', 'post'],
    '/profile/filter[/order/{orderby}]',
    'GLPI\Telemetry\Controllers\Profile:filter'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterProfileReferences');


//Reference registration
$app->post('/reference', 'GLPI\Telemetry\Controllers\Reference:register')
   //->add($recaptcha)
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

// run slim
$app->run();
