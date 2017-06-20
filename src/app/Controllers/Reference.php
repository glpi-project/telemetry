<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;
use App\Models\Reference as ReferenceModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Slim\Http\Request;
use Slim\Http\Response;

class Reference extends ControllerAbstract {

   public function view(Request $req,  Response $res) {
      $orderby = "created_at";
      $sort    = "dec";
      $get     = $req->getQueryParams();
      if (isset($get['orderby'])) {
         $orderby = $get['orderby'];
      }
      if (isset($get['sort'])) {
         $sort = $get['sort'];
      }

      $references = ReferenceModel::orderBy($orderby, $sort)
                           ->paginate(15);

      $countries_json = file_get_contents("../vendor/mledoze/countries/dist/countries.json");
      $countries      = json_decode($countries_json, true) ;

      $this->render('reference.html', [
         'references' => $references,
         'pagination' => $references->appends($_GET)->render(),
         'countries'  => $countries,
         'orderby'    => $orderby,
         'sort'       => $sort
      ]);
   }

   public function register(Request $req,  Response $res) {
      $post = $req->getParsedBody();

      // alter data
      $post['num_assets']   = (int) $post['num_assets'];
      $post['num_helpdesk'] = (int) $post['num_helpdesk'];
      $post['country']      = strtolower($post['country']);

      // create in db
      ReferenceModel::create($post);

      // redirect to list
      return $res->withRedirect('/reference');
   }
}
