<?php namespace App\Controllers;

use App\Controllers\ControllerAbstract;
use App\Models\Reference as ReferenceModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Slim\Http\Request;
use Slim\Http\Response;

class Reference extends ControllerAbstract {

   public function view(Request $req,  Response $res) {
      $get = $req->getQueryParams();

      // default session param for this controller
      if (!isset($_SESSION['reference'])) {
         $_SESSION['reference'] = [
            "orderby" => 'created_at',
            "sort"    => "desc"
         ];
      }

      // manage sorting
      if (isset($get['orderby'])) {
         if ($_SESSION['reference']['orderby'] == $get['orderby']) {
            // toggle sort if orderby requested on the same column
            $_SESSION['reference']['sort'] = ($_SESSION['reference']['sort'] == "desc"
                                                ? "asc"
                                                : "desc");
         }
         $_SESSION['reference']['orderby'] = $get['orderby'];
      }

      // retrieve data from model
      $references = ReferenceModel::where('is_displayed', true)
                           ->orderBy($_SESSION['reference']['orderby'],
                                     $_SESSION['reference']['sort'])
                           ->paginate(15);

      $references->setPath($this->container->get('settings')['baseurl']."reference");

      // render in twig view
      $this->render('reference.html', [
         'total'      => ReferenceModel::where('is_displayed', true)->count(),
         'class'      => 'reference',
         'showmodal'  => isset($get['showmodal']),
         'uuid'       => isset($get['uuid']) ? $get['uuid'] : '',
         'references' => $references,
         'pagination' => $references->appends($_GET)->render(),
         'orderby'    => $_SESSION['reference']['orderby'],
         'sort'       => $_SESSION['reference']['sort']
      ]);
   }

   public function register(Request $req,  Response $res) {
      $post = $req->getParsedBody();

      // alter data
      $post['num_assets']   = (int) $post['num_assets'];
      $post['num_helpdesk'] = (int) $post['num_helpdesk'];
      $post['country']      = strtolower($post['country']);

      // clean data
      unset($post['g-recaptcha-response']);
      unset($post['csrf_name']);
      unset($post['csrf_value']);

      // create reference in db
      ReferenceModel::updateOrCreate(['uuid' => $post['uuid']], $post);

      // send a mail to admin
      $mail = new \PHPMailer;
      $mail->setFrom($this->container['settings']['mail_from']);
      $mail->addAddress($this->container['settings']['mail_admin']);
      $mail->Subject = "A new reference has been submitted: ".$post['name'];
      $mail->Body    = var_export($post, true);
      $mail->send();

      // store a message for user (displayed after redirect)
      $this->container->flash->addMessage('success',
         'Your reference has been stored! An administrator will moderate it before display on the site.');

      // redirect to ok page
      return $res->withRedirect('./ok');
   }
}
