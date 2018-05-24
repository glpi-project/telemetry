<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\User as UserModel;
use GLPI\Telemetry\Models\Register as RegisterModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Profile extends ControllerAbstract
{
    public function view(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $diff_filters = ReferenceModel::setDifferentsFilters($get, $args, $_SESSION['reference'], __CLASS__);
        $_SESSION['reference'] = $diff_filters;

        //Reload SESSION variables for user's references
        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();
        $_SESSION['user']['references_count'] = $ref_model->where('user_id', $_SESSION['user']['id'])->get()->count();

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
                $model->where('user_id', '=', $_SESSION['user']['id']);
                $model->orderBy(
                    $order_table . '.' . $order_field,
                    $_SESSION['reference']['sort']
                )
                    ->leftJoin($join_table, 'reference.id', '=', $join_table . '.reference_id')
                ;
                $references = $model->paginate($_SESSION['reference']['pagination']);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '42P01') {
                    //rlation does not exists
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
