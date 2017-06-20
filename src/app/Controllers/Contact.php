<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;

class Contact  extends ControllerAbstract {
   public function view() {
      $this->render('contact.html', [
         'class' => 'contact'
      ]);
   }
}
