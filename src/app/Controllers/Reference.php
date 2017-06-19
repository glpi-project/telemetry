<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;
use App\Models\Reference as ReferenceModel;
use Illuminate\Pagination\LengthAwarePaginator;

class Reference extends ControllerAbstract {
   public function view(\Slim\Http\Request $req,  \Slim\Http\Response $res) {
      $references     = ReferenceModel::paginate(15);
      $countries_json = file_get_contents("../vendor/mledoze/countries/dist/countries.json");
      $countries      = json_decode($countries_json, true) ;

      $this->render('reference.html', [
         'references' => $references,
         'pagination' => $references->appends($_GET)->render(),
         'countries'  => $countries
      ]);
   }
}
