<?php

namespace App\Controllers;

use App\Controllers\ControllerAbstract;

class Reference extends ControllerAbstract {
   public function view() {
      $this->render('reference.html');
   }
}
