<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// include user configuration
require '../config.inc.php';

// autoload composer libs
require '../vendor/autoload.php';

// manage our own classes
spl_autoload_register(function ($classname) {
   require ("./" . $classname . ".php");
});

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

// setup db connection
$container['db'] = function ($container) {
   $db  = $container['settings']['db'];
   $pdo = new PDO("pgsql:host=".$db['host'].";dbname=" . $db['dbname'], $db['user'], $db['pass']);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

   return $pdo;
};

// setup twig
$container['view'] = function ($container) {
   $view = new \Slim\Views\Twig('../templates', [
      'cache' => $container['settings']['debug'] ? false : '../cache'
   ]);

   // Instantiate and add Slim specific extension
   $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
   $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

   return $view;
};