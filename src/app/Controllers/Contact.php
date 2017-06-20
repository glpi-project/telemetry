<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;
use Slim\Http\Request;
use Slim\Http\Response;

class Contact  extends ControllerAbstract {
   public function view() {
      $this->render('contact.html', [
         'class' => 'contact'
      ]);
   }

   public function send(Request $req,  Response $res) {
      
   }
}
