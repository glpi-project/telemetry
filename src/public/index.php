<?php

require '../inc/init.php';

// route: default
$app->get('/', function ($request, $response, $args) {
   return $this->view->render($response, 'index.html');
});

$app->get('/contact', function ($request, $response, $args) {
   return $this->view->render($response, 'contact.html');
});

$app->get('/references', function ($request, $response, $args) {
   return $this->view->render($response, 'references.html');
});

// route: post json file
$app->post('/send_json/', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
});

// run slim
$app->run();
