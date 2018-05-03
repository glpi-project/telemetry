<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use Slim\Http\Request;
use Slim\Http\Response;

class Connection extends ControllerAbstract
{
    public function view()
    {
        $this->render($this->container->project->pathFor('connection.html.twig'), [
         'class' => 'connection'
        ]);
    }
}
