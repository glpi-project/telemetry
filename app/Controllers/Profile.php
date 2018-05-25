<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\PageAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\User as UserModel;
use GLPI\Telemetry\Models\Register as RegisterModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Profile extends PageAbstract
{
    public function view(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $_SESSION['reference'] = $this->setDifferentsFilters($get, $args);

        //Reload SESSION variables for user's references
        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();
        $_SESSION['user']['references_count'] = $ref_model->where('user_id', $_SESSION['user']['id'])->get()->count();

        $refs_tab = $this->load_refs(true);
        $references = $refs_tab['references'];
        $dyn_refs = $refs_tab['dyn_refs'];

        $references->setPath($this->container->router->pathFor('profile'));

        // render in twig view
        $this->render($this->container->project->pathFor('profile.html.twig'), [
            'class'         => 'profile',
            'uuid'          => isset($get['uuid']) ? $get['uuid'] : '',
            'references'    => $references,
            'pagination'    => $references->appends($_GET)->render(),
            'orderby'       => $_SESSION['reference']['orderby'],
            'sort'          => $_SESSION['reference']['sort'],
            'dyn_refs'      => $dyn_refs,
            'user_session'	=> $_SESSION['user'],
            'status_page'   => $_SESSION['reference'][__CLASS__]
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

        return $res->withRedirect($this->container->router->pathFor('profile'));
    }

    public function userUpdate(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        $user = htmlentities($post['name']);
        $mail = htmlentities($post['mail']);
        $pass = htmlentities($post['new_password']);
        $confirm_pass = htmlentities($post['confirm_password']);

        $user_ref = new UserModel();
        $user_model = $user_ref->newInstance();

        $register_ref = new RegisterModel();
        $register_model = $register_ref->newInstance();

        $tmp = 
        [
            'username' => $user,
            'email' => $mail
        ];

        if(empty($pass) xor empty($confirm_pass) || $pass !== $confirm_pass || !$register_model->is_valid_password($pass)){
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'warn',
                'There is a problem with your password. Can\'t update your profile'
            );
            // redirect to ok page
            return $res->withRedirect($this->container->router->pathFor('profile'));
        } elseif(!empty($pass) && !empty($confirm_pass)) {
            $tmp['hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }



        if(!empty($user) && preg_match('/[a-zA-Z]/', $user)){

            if($user_model->usernameExist($user) && $user != $_SESSION['user']['username']){
                // store a message for user (displayed after redirect)
                $this->container->flash->addMessage(
                    'warn',
                    'This username already exist. Can\'t update your profile'
                );
                // redirect to ok page
                return $res->withRedirect($this->container->router->pathFor('profile'));
            }
            $user_model->where('username', '=', $_SESSION['user']['username'])->update($tmp);
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'success',
                'Update done !'
            );

            //reload user informations
            $_SESSION['user'] = $user_model->getUser($user)['attributes'];

        } else {
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'warn',
                'You must fill the username field with letters to update your profile.'
            );
        }

        // redirect to ok page
        return $res->withRedirect($this->container->router->pathFor('profile'));
    }
}
