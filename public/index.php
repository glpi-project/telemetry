<?php

require '../app/init.php';

// route: default
$app->get('/', 'App\Controllers\Telemetry:view');

// contact
$app->get('/contact', 'App\Controllers\Contact:view')
   ->add(new App\Middleware\CsrfView($container))
   ->add($container['csrf']);
$app->post('/contact', 'App\Controllers\Contact:send')
   ->add($recaptcha)
   ->add($container['csrf']);

// reference
$app->get('/reference', 'App\Controllers\Reference:view')
   ->add(new App\Middleware\CsrfView($container))
   ->add($container['csrf']);
$app->post('/reference', 'App\Controllers\Reference:register')
   ->add($recaptcha)
   ->add($container['csrf']);

// telemetry
$app->get('/telemetry', 'App\Controllers\Telemetry:view');
$app->post('/telemetry', 'App\Controllers\Telemetry:send')
   ->add(new \App\Middleware\JsonCheck($container));

// special pages
$app->get('/ok', function ($request, $response, $args) {
   return $this->view->render($response, 'ok.html', [
      'class' => 'ok'
   ]);
});

// run slim
$app->run();
