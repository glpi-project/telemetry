<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use Slim\Http\Request;
use Slim\Http\Response;

class Register extends ControllerAbstract
{
    public function view()
    {
        $this->render($this->container->project->pathFor('register.html.twig'), [
         'class' => 'register'
        ]);
    }
}
