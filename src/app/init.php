<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// include user configuration
$config = require __DIR__ .  '/../config.inc.php';

// autoload composer libs
require __DIR__ . '/../vendor/autoload.php';

// init slim
$app       = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

// setup monolog
$container['logger'] = function($c) {
   $logger       = new \Monolog\Logger('my_logger');
   $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
   $logger->pushHandler($file_handler);

   return $logger;
};

// setup twig
$container['view'] = function ($container) {
   $view = new \Slim\Views\Twig('../app/Templates', [
      'cache' => $container['settings']['debug'] ? false : '../app/Templates/Cache'
   ]);

   // Instantiate and add Slim specific extension
   $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
   $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
   $view->addExtension(new Twig_Extension_Debug());

   return $view;
};

// setup db connection
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->bootEloquent();
