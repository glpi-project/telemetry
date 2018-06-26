<?php namespace GLPI\Telemetry\Controllers;

use Slim\Views\Twig;
use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;
use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\User as UserModel;

abstract class PageAbstract extends ControllerAbstract
{
    /**
     * This function load references and dynamics references
     *
     * @param String $management_name Define wich type of page we are 'reference_management', 'profile', 'users_management'
     * @param String $user_id Specify if the function will have to load references for a specific user
     * @param integer $status Specify if the function will have to load references for a specific status
     *
     * @return array ['references', 'dyn_refs'] to return references and dynamics references
     **/
    public function loadRefs($management_name, $user_id = null, $status = null)
    {
        $status = ($status === null) ? $_SESSION[$management_name]['customFilter'] : $status;

        //check for refences presence
        $dyn_refs = $this->container->project->getDynamicReferences();
        if (false === $dyn_refs) {
             // retrieve data from model
            $references = ReferenceModel::active()->orderBy(
                $_SESSION[$management_name]['orderby'],
                $_SESSION[$management_name]['sort']
            )->paginate($_SESSION[$management_name]['pagination']);
        } else {
            try {
                $join_table = $this->container->project->getSlug() . '_reference';
                $order_field = $_SESSION[$management_name]['orderby'];
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
                $model->where('status', '=', $status);
                if ($user_id != null) {
                    $model->where('user_id', '=', $user_id);
                }
                $search = $_SESSION[$management_name]['search'];
                if ($search != 'null') {
                    $model->whereRaw('LOWER("'.strtolower($_SESSION[$management_name]['search_on']).'") LIKE ? ', ['%'.htmlentities(strtolower($search)).'%']);
                }

                if ($order_field != null) {
                    $model->orderBy(
                        $order_table . '.' . $order_field,
                        $_SESSION[$management_name]['sort']
                    );
                }
                    $model->leftJoin($join_table, 'reference.id', '=', $join_table . '.reference_id');
                $references = $model->paginate($_SESSION[$management_name]['pagination']);
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
        return ['references' => $references, 'dyn_refs' => $dyn_refs];
    }

    /**
     * Load users from database based on filters controller
     *
     * @param String $order_field
     *
     * @return GLPI\Telemetry\Models\User
     */
    public function loadUsers($order_field = 'username')
    {
        $ref = new UserModel();
        $model = $ref->newInstance();
        $is_admin = $model->stringBoolAdmin($_SESSION['users_management']['customFilter']);
        $search = $_SESSION['users_management']['search'];
        $model = call_user_func_array(
            [
                $model,
                'select'
            ],
            ['users.*']
        );

        if ($is_admin !== null) {
            $model->where('is_admin', '=', $is_admin);
        }

        if ($search != 'null') {
            $model->whereRaw('LOWER("'.strtolower($_SESSION['users_management']['search_on']).'") LIKE ? ', ['%'.htmlentities(strtolower($search)).'%']);
        }
        
        $model->orderBy(
            $order_field,
            $_SESSION['users_management']['sort']
        );
        $users = $model->paginate($_SESSION['users_management']['pagination']);

        foreach ($users as $key => $user) {
            $user['attributes'] =
                $user['attributes'] +
                ['refs_count'=>$this->loadUserRefsCount($user['attributes']['id'])];
        }

        return $users;
    }

    /**
     * Update reference status
     *
     * @param integer $user_id
     *
     * @return GLPI\Telemetry\Models\Reference
     */
    public function loadUserRefsCount($user_id = null)
    {
        if ($user_id == null) {
            $user_id = $_SESSION['user']['id'];
        }

        //Reload SESSION variables for user's references
        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();
        return $ref_model->where('user_id', $user_id)->get()->count();
    }

    /**
     * Associating for any action code an array for the update
     *
     * @param String $action
     *
     * @return array
     */
    public function actionsToUpdateArray($action)
    {
        switch ($action) {
            case 'to_admin':
                return ['is_admin' => true];
                break;

            case 'to_not_admin':
                return ['is_admin' => false];
                break;

            case 'to_pending':
                return ['status' => ReferenceModel::PENDING];
                break;

            case 'to_denied':
                return ['status' => ReferenceModel::DENIED];
                break;

            case 'to_accepted':
                return ['status' => ReferenceModel::ACCEPTED];
                break;
            
            default:
                return null;
                break;
        }
    }

    /**
     * Make the action for admin management
     *
     * @param array $args Specidied the type page : 'reference_management', 'users_management', 'profile'
     *
     * @return Slim\Http\Response
     */
    public function doActions(Request $req, Response $res, $args)
    {
        $post = $req->getParsedBody();
        unset($post['csrf_name']);
        unset($post['csrf_value']);

        foreach ($post as $key => $value) {
            $tmp = explode("-", $key);
            switch ($tmp[0]) {
                case 'select':
                    //$actions[object.id] = action.code
                    $actions[$tmp[1]] = $value;
                    break;
                case 'checkbox':
                    $objects_id[] = $tmp[1];
                    break;
                
                default:
                    $res
                    ->write($container->flash->addMessage('error', 'Something went wrong, you were redirected.'))
                    ->withRedirect($container->router->pathFor('telemetry'));
                    break;
            }
        }

        switch ($args['type_page']) {
            case 'users_management':
                $ref = new UserModel();
                break;

            case 'reference_management':
                $ref = new ReferenceModel();
                break;
            
            default:
                $res
                ->write($container->flash->addMessage('error', 'Something went wrong, you were redirected.'))
                ->withRedirect($container->router->pathFor('telemetry'));
                break;
        }

        $model = $ref->newInstance();

        foreach ($actions as $object_id => $action) {
            if (in_array($object_id, $objects_id)) {
                $arrayActions = $this->actionsToUpdateArray($action);
                if ($arrayActions === null) {
                    return false;
                } else {
                    $action_res = $model->where('id', '=', $object_id)->update($arrayActions);
                }
            }
        }
        unset($post);
        return $this->typePageRedirect($req, $res, $args['type_page']);
    }

    /**
     * Make the action for admin management
     *
     * @param String $type_page Specidied the type page : 'reference_management', 'users_management', 'profile'
     *
     * @return Slim\Http\Response
     */
    public function typePageRedirect(Request $req, Response $res, $type_page)
    {
        switch ($type_page) {
            case 'users_management':
                return $res->withRedirect($this->container->router->pathFor('adminUsersManagement'));
                break;

            case 'reference_management':
                return $res->withRedirect($this->container->router->pathFor('adminReferencesManagement'));
                break;

            case 'profile':
                return $res->withRedirect($this->container->router->pathFor('profile'));
                break;

            case 'reference':
                return $res->withRedirect($this->container->router->pathFor('reference'));
                break;
            
            default:
                return $res->withRedirect($this->container->router->pathFor('telemetry'));
                break;
        }
    }
}
