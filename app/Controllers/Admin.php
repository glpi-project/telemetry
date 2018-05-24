<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Admin extends ControllerAbstract
{
    public function view(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $diff_filters = ReferenceModel::setDifferentsFilters($get, $args, $_SESSION['reference'], __CLASS__);
        $_SESSION['reference'] = $diff_filters;

        //check for refences presence
        $dyn_refs = $this->container->project->getDynamicReferences();
        if (false === $dyn_refs) {
             // retrieve data from model
            $references = ReferenceModel::active()->orderBy(
                $_SESSION['reference']['orderby'],
                $_SESSION['reference']['sort']
            )->paginate($_SESSION['reference']['pagination']);
        } else {
            try {
                $join_table = $this->container->project->getSlug() . '_reference';
                $order_field = $_SESSION['reference']['orderby'];
                $order_table = (isset($dyn_refs[$order_field]) ? $join_table : 'reference');
                // retrieve data from model
                $ref = new ReferenceModel();
                $model = $ref->newInstance();
                $model = call_user_func_array(
                    [
                        $model,
                        'select'
                    ],
                    array_merge(
                        ['reference.*'],
                        array_map(
                            function ($key) use ($join_table) {
                                return $join_table . '.' . $key;
                            },
                            array_keys($dyn_refs)
                        )
                    )
                );
                $model->where('status', '=', $_SESSION['reference'][__CLASS__]);
                $model->orderBy(
                    $order_table . '.' . $order_field,
                    $_SESSION['reference']['sort']
                )
                    ->leftJoin($join_table, 'reference.id', '=', $join_table . '.reference_id')
                ;
                $references = $model->paginate($_SESSION['reference']['pagination']);
                //var_dump($_SESSION['reference']);die;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '42P01') {
                    //relation does not exists
                    throw new \RuntimeException(
                        'You have configured dynamic references for your project; but table ' .
                        $join_table . ' is missing!',
                        0,
                        $e
                    );
                }
                throw $e;
            }
        }

        $ref_ref = new ReferenceModel;
        $ref = $ref_ref->newInstance();
        foreach ($references as $reference) {
        	$mails = $ref->findMails($reference['attributes']['id']);
        	if($mails !== false){
        		$ref_user_mails[$reference['attributes']['id']] = $mails;
        	}else{
        		$ref_user_mails[$reference['attributes']['id']] = null;
        	}
        }



        $references->setPath($this->container->router->pathFor('admin'));

        // render in twig view
        $this->render($this->container->project->pathFor('admin.html.twig'), [
            'class'         => 'admin',
            'showmodal'     => isset($get['showmodal']),
            'uuid'          => isset($get['uuid']) ? $get['uuid'] : '',
            'references'    => $references,
            'pagination'    => $references->appends($_GET)->render(),
            'orderby'       => $_SESSION['reference']['orderby'],
            'sort'          => $_SESSION['reference']['sort'],
            'dyn_refs'      => $dyn_refs,
            'user_session'	=> $_SESSION['user'],
            'status_page'	=> $_SESSION['reference'][__CLASS__],
            'ref_user_mails'=> json_encode($ref_user_mails)	
        ]);
    }

    public function filter(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        // manage sorting
        if (isset($args['orderby'])) {
            if ($_SESSION['reference']['orderby'] == $args['orderby']) {
               // toggle sort if orderby requested on the same column
                $_SESSION['reference']['sort'] = ($_SESSION['reference']['sort'] == "desc"
                                                ? "asc"
                                                : "desc");
            }
            $_SESSION['reference']['orderby'] = $args['orderby'];
        }

        return $res->withRedirect($this->container->router->pathFor('admin'));
    }

    public function ActionReferencePost(Request $req, Response $res)
    {
    	$post = $req->getParsedBody();
    	$tmp = [];
    	if(isset($post['checkboxAdminActionForm1'])){
    		$tmp[] = $post['checkboxAdminActionForm1'];
    	}
    	if(isset($post['checkboxAdminActionForm2'])){
    		$tmp[] = $post['checkboxAdminActionForm2'];
    	}
    	if(isset($post['inputrow3col2'])){
    		$tmp[] = $post['inputrow3col2'];
    	}

    	return $this->ActionReference($req, $res,
    		[
    			'ref_id' => $post['ref_id_input'],
    			'status' => $post['status_input'],
    			'comment' => $post['comment'],
    			'mails'	=> $tmp
    		]
    	);
    }

    public function ActionReference(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();
        $ref_ref = new ReferenceModel;
        $ref = $ref_ref->newInstance();

        $ref_status_before_update_res = $ref->where("id", "=", $args['ref_id'])->firstOrFail();
        $ref_status_before_update = $ref_status_before_update_res['attributes']['status'];

        $res_update = $ref->updateStatus($args['ref_id'], $args['status']);

        if($res_update == 1){
        	$type = "success";
        	$msg_text = "Action done";
        	foreach ($args['mails'] as $key => $mail_to) {
        		$this->sendMail($req, $res, $args, $ref_status_before_update, $mail_to);
        	}
        }else{
        	$type = "error";
        	$msg_text = "An error happened, bad insert, $res_update rows were updated, 1 row to update was expected";
        }

        $this->container->flash->addMessage(
            $type,
            $msg_text
        );

        return $res->withRedirect($this->container->router->pathFor('sorterAdmin', ['status'=>$ref_status_before_update]));
    }

    public function sendMail(Request $req, Response $res, array $args, $status_from, $mail_to)
    {

        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();

        $status_from = $ref_model->statusIntToText($status_from);
        $status_to = $ref_model->statusIntToText($args['status']);

        $join_table = $this->container->project->getSlug() . '_reference';

        $res_ref = $ref_model->where("reference.id", "=", $args['ref_id'])        
        ->leftJoin($join_table, $join_table.'.reference_id', '=', 'reference.id')
        ->firstOrFail();

        if(isset($args['comment']) && !empty($args['comment'])){
        	$admin_msg = "Administrator's message :\n".$args['comment'];
        }else{
        	$admin_msg = "";
        }

        $msg_ref = 
        "
        Name : " . $res_ref['attributes']['name'] ."\n
        Country : " . $res_ref['attributes']['country'] ."\n
        Assets : " . $res_ref['attributes']['num_assets'] ."\n
        Helpdesk : " . $res_ref['attributes']['num_helpdesk'] ."\n
        Registration date : " . date("d M Y, h:i a",strtotime($res_ref['attributes']['created_at'])) ."\n
        Comment : " . $res_ref['attributes']['comment']."\n";

        $msg = "The status of your reference below has been changed by the admin from $status_from to $status_to :\n$msg_ref\n$admin_msg";

        // prepare mail
        $mail = new \PHPMailer;
        $mail->setFrom($this->container['settings']['mail_admin']);
        $mail->addAddress($mail_to);
        $mail->Subject = "A new message from telemetry site : Reference status changed";
        $mail->Body    = $msg;
        $send_ok = $mail->send();

        if($send_ok === false){
	        $this->container->flash->addMessage(
	            "error",
	            "Error sending the mail to $mail_to.\n".$mail->ErrorInfo
	        );
        	return false;
        } else {
        	return true;
        }
    }
}