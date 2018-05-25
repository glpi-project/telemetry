<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Register as RegisterModel;
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

    public function send(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        $register_ref = new RegisterModel;
        $register_model = $register_ref->newInstance();

        $tab = $register_model->checkRegister($post);

        if ($tab['status'] === '200') {
            $type = 'success';
            $redirect = 'connection';
        } else {
            $type = 'error';
            $redirect = 'register';
        }

        $msg_text = $tab['msg'];
        $this->container->flash->addMessage(
            $type,
            $msg_text
        );


        //redirect
        return $res->withRedirect($this->container->router->pathFor($redirect));
    }
}
