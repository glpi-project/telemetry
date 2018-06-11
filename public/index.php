<?php

require '../app/init.php';

// route: default
$app->get('/', 'GLPI\Telemetry\Controllers\Telemetry:view')
    ->setName('telemetry');

// Filters on app


if ($container->project->hasContactPage()) {
    // contact
    $app->get('/contact', 'GLPI\Telemetry\Controllers\Contact:view')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('contact');
    $app->post('/contact', 'GLPI\Telemetry\Controllers\Contact:send')
        //->add($recaptcha)
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
    $app->get('/profile[/pagination/{pagination}[/customFilter/{customFilter}[/search/{search}[/page/{page:\d+}]]]]', 'GLPI\Telemetry\Controllers\Profile:view')
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

    //Profile user update
    $app->map(
        ['get', 'post'],
        '/profile/user/update',
        'GLPI\Telemetry\Controllers\Profile:userUpdate'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('actionProfileUserUpdate');
}

if ($container->project->hasAdminPage()) {
    // admin
    $app->get('/admin/references[/page/{page:\d+}]', 'GLPI\Telemetry\Controllers\Admin:viewReferencesManagement')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('adminReferencesManagement');

    $app->get('/admin/users[/page/{page:\d+}]', 'GLPI\Telemetry\Controllers\Admin:viewUsersManagement')
        ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
        ->add($container['csrf'])
        ->setName('adminUsersManagement');

    //Admin users management
    $app->map(
        ['get', 'post'],
        '/admin/usersActions[/type_page/{type_page}]',
        'GLPI\Telemetry\Controllers\Admin:doActions'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('doUsersActions');

    //Admin users management
    $app->map(
        ['get', 'post'],
        '/admin/referencesActions[/type_page/{type_page}]',
        'GLPI\Telemetry\Controllers\Admin:doActions'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('doReferencesActions');

    //Admin denied
    $app->map(
        ['get', 'post'],
        '/admin/prepareMails',
        'GLPI\Telemetry\Controllers\Admin:prepareMails'
    )
       ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
       ->add($container['csrf'])
       ->setName('actionAdminReferencesWithMsg');
}


/** References */
//References list
$app->get('/reference[/page/{page:\d+}]', 'GLPI\Telemetry\Controllers\Reference:view')
    ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
    ->add($container['csrf'])
    ->setName('reference');

//App filters
$app->map(
    ['get', 'post'],
    '/telemetry/WithoutFilter[/type_page/{type_page}]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('withoutFilters');

$app->map(
    ['get', 'post'],
    '/telemetry/filter[/type_page/{type_page}[/order/{orderby}]]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterOrderBy');

$app->map(
    ['get', 'post'],
    '/telemetry/paginationfilter[/type_page/{type_page}[/pagination/{pagination}]]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterPagination');

$app->map(
    ['get', 'post'],
    '/telemetry/customfilter[/type_page/{type_page}[/customFilter/{customFilter}]]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterCustomFilter');

$app->map(
    ['get', 'post'],
    '/telemetry/actionfilter[/type_page/{type_page}[/action_code/{action_code}]]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterAction');

$app->map(
    ['get', 'post'],
    '/telemetry/searchfilter[/type_page/{type_page}[/search/{search}]]',
    'GLPI\Telemetry\Controllers\Filters:setDifferentsFilters'
)
   ->add(new GLPI\Telemetry\Middleware\CsrfView($container))
   ->add($container['csrf'])
   ->setName('filterSearch');

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
