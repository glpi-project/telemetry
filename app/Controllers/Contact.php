<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use Slim\Http\Request;
use Slim\Http\Response;

class Contact extends ControllerAbstract
{
    public function view()
    {
        $this->render($this->container->project->pathFor('contact.html'), [
         'class' => 'contact'
        ]);
    }

    public function send(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        // prepare mail
        $mail = new \PHPMailer;
        $mail->setFrom($post['email']);
        $mail->addAddress($this->container['settings']['mail_admin']);
        $mail->Subject = "A new message from telemetry site: ".$post['subject'];
        $mail->Body    = $post['message'];
        $mail->send();

       // store a message for user (displayed after redirect)
        $this->container->flash->addMessage(
            'success',
            'Thanks for your message, please wait a bit for our answer !'
        );

        //redirect
        return $res->withRedirect($this->container->router->pathFor('telemetry'));
    }
}
