<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\PageAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\User as UserModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Admin extends PageAbstract
{
    public function viewUsersManagement(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $users = $this->loadUsers();

        $users->setPath($this->container->router->pathFor('adminUsersManagement'));

        $this->render($this->container->project->pathFor('adminUsersManagement.html.twig'), [
            'class'        => 'admin',
            'showmodal'    => isset($get['showmodal']),
            'users'        => $users,
            'uuid'         => isset($get['uuid']) ? $get['uuid'] : '',
            'user_session' => $_SESSION['user'],
            'actions'      => $this->getUsersActions(),
            'pagination'   => $_SESSION['users_management']['pagination'],
            'orderby'      => $_SESSION['users_management']['orderby'],
            'sort'         => $_SESSION['users_management']['sort'],
            'customFilter' => $_SESSION['users_management']['customFilter'],
            'search'       => $_SESSION['users_management']['search'],
            'action_code'  => $_SESSION['users_management']['action_code'],
            'search_on'    => $_SESSION['users_management']['search_on'],
            'type_page'    => 'users_management'
        ]);
    }

    public function viewReferencesManagement(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $refs_tab = $this->loadRefs('reference_management', false);
        $references = $refs_tab['references'];
        $dyn_refs = $refs_tab['dyn_refs'];

        $ref_ref = new ReferenceModel;
        $ref = $ref_ref->newInstance();
        foreach ($references as $reference) {
            $arr_ref_id_user[$reference['attributes']['id']] = $ref->findUsername($reference['attributes']['id']);
            $mails = $ref->findMails($reference['attributes']['id']);
            if ($mails !== false) {
                $ref_user_mails[$reference['attributes']['id']] = $mails;
            } else {
                $ref_user_mails[$reference['attributes']['id']] = null;
            }
        }

        $references->setPath($this->container->router->pathFor('adminReferencesManagement'));

        // render in twig view
        $this->render($this->container->project->pathFor('adminReferencesManagement.html.twig'), [
            'class'         => 'admin',
            'showmodal'     => isset($get['showmodal']),
            'uuid'          => isset($get['uuid']) ? $get['uuid'] : '',
            'references'    => $references,
            'pagination'    => $_SESSION['reference_management']['pagination'],
            'orderby'       => $_SESSION['reference_management']['orderby'],
            'sort'          => $_SESSION['reference_management']['sort'],
            'dyn_refs'      => $dyn_refs,
            'user_session'  => $_SESSION['user'],
            'actions'       => $this->getReferencesActions(),
            'customFilter'  => $_SESSION['reference_management']['customFilter'],
            'search'        => $_SESSION['reference_management']['search'],
            'action_code'   => $_SESSION['reference_management']['action_code'],
            'search_on'     => $_SESSION['reference_management']['search_on'],
            'type_page'     => 'reference_management',
            'ref_user_mails'=> json_encode($ref_user_mails),
            'arr_ref_id_user'=> $arr_ref_id_user
        ]);
    }

    public function prepareMails(Request $req, Response $res)
    {
        $post = $req->getParsedBody();
        $tmp = [];
        if (isset($post['checkboxAdminActionForm1'])) {
            $tmp[] = $post['checkboxAdminActionForm1'];
        }
        if (isset($post['checkboxAdminActionForm2'])) {
            $tmp[] = $post['checkboxAdminActionForm2'];
        }
        if (isset($post['inputrow3col2'])) {
            $tmp[] = $post['inputrow3col2'];
        }

        foreach ($tmp as $key => $value) {
            $this->sendMail(
                $req,
                $res,
                [
                    'ref_id' => $post['ref_id_input'],
                    'comment' => $post['comment'],
                    'mail' => $value
                ]
            );
        }
        return $res->withRedirect($this->container->router->pathFor('adminReferencesManagement'));
    }

    public function sendMail(Request $req, Response $res, array $args)
    {
        $mail_to = $args['mail'];
        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();

        $join_table = $this->container->project->getSlug() . '_reference';

        $res_ref = $ref_model->where("reference.id", "=", $args['ref_id'])
        ->leftJoin($join_table, $join_table.'.reference_id', '=', 'reference.id')
        ->firstOrFail();

        if (isset($args['comment']) && !empty($args['comment'])) {
            $admin_msg = "Administrator's message :\n".$args['comment'];
        } else {
            $admin_msg = "";
        }

        $msg_ref =
        "
        Name : " . $res_ref['attributes']['name'] ."\n
        Country : " . $res_ref['attributes']['country'] ."\n
        Assets : " . $res_ref['attributes']['num_assets'] ."\n
        Helpdesk : " . $res_ref['attributes']['num_helpdesk'] ."\n
        Registration date : " . date("d M Y, h:i a", strtotime($res_ref['attributes']['created_at'])) ."\n
        Comment : " . $res_ref['attributes']['comment']."\n";

        $msg = "The administrator send you a message about your reference below :\n$msg_ref\n$admin_msg";

        // prepare mail
        $mail = new \PHPMailer;
        $mail->setFrom($this->container['settings']['mail_admin']);
        $mail->addAddress($mail_to);
        $mail->Subject = "A new message from telemetry site :";
        $mail->Body    = $msg;
        $send_ok = $mail->send();

        if ($send_ok === false) {
            $this->container->flash->addMessage(
                "error",
                "Error sending the mail to $mail_to.\n".$mail->ErrorInfo
            );
        }
    }

    public function getUsersActions()
    {
        return [
            ['code' => 'to_admin', 'msg' => 'Upgrade user to admin'],
            ['code' => 'to_not_admin', 'msg' => 'Downgrade this admin user']
         ];
    }

    public function getReferencesActions()
    {
        return [
            ['code' => 'to_denied', 'msg' => 'Status to denied'],
            ['code' => 'to_pending', 'msg' => 'Status to pending'],
            ['code' => 'to_accepted', 'msg' => 'Status to accepted']
         ];
    }
}
