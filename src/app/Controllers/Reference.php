<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;
use App\Models\Reference as ReferenceModel;
use Illuminate\Pagination\LengthAwarePaginator;

class Reference extends ControllerAbstract {
   public function view(\Slim\Http\Request $req,  \Slim\Http\Response $res) {
      $references = ReferenceModel::paginate(15);

      $this->render('reference.html', [
         'references' => $references,
         'pagination' => $references->appends($_GET)->render()
      ]);
   }
}
