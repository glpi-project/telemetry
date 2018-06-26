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

        //Reload SESSION variables for user's references
        $_SESSION['user']['references_count'] = $this->loadUserRefsCount();

        $refs_tab = $this->loadRefs('profile', $_SESSION['user']['id']);
        $references = $refs_tab['references'];
        $dyn_refs = $refs_tab['dyn_refs'];

        $references->setPath($this->container->router->pathFor('profile'));

        // render in twig view
        $this->render($this->container->project->pathFor('profile.html.twig'), [
            'class'         => 'profile',
            'uuid'          => isset($get['uuid']) ? $get['uuid'] : '',
            'references'    => $references,
            'pagination'    => $_SESSION['profile']['pagination'],
            'orderby'       => $_SESSION['profile']['orderby'],
            'sort'          => $_SESSION['profile']['sort'],
            'dyn_refs'      => $dyn_refs,
            'user_session'  => (object) $_SESSION['user'],
            'status_page'   => $_SESSION['profile']['customFilter'],
            'customFilter'  => $_SESSION['profile']['customFilter'],
            'search'        => $_SESSION['profile']['search'],
            'search_on'     => $_SESSION['profile']['search_on'],
            'type_page'     => 'profile'
        ]);
    }

    public function userUpdate(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        $user = utf8_encode($post['name']);
        $user = htmlentities($post['name']);



        $mail = utf8_encode($post['mail']);
        $mail = htmlentities($post['mail']);

        $pass = utf8_encode($post['new_password']);
        $pass = htmlentities($post['new_password']);

        $confirm_pass = utf8_encode($post['confirm_password']);
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

        if (empty($pass) xor empty($confirm_pass) || $pass !== $confirm_pass || !$register_model->isValidPassword($pass)) {
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'warn',
                'There is a problem with your password. Can\'t update your profile'
            );
            // redirect to ok page
            return $res->withRedirect($this->container->router->pathFor('profile'));
        } elseif (!empty($pass) && !empty($confirm_pass)) {
            $tmp['hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }



        if (!empty($user) && preg_match('/[a-zA-Z]/', $user)) {
            if ($user_model->usernameExist($user) && $user != $_SESSION['user']['username']) {
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
