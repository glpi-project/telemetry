<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class Profile extends ControllerAbstract
{
    public function view(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        /**
         * if status is not specified in parameter $args :
         * default -> pending
         * and if the status is specified in the $_SESSION, we'll take it.
         * That allow the pagination doesn't specified any status parameter.
        **/
        if(!isset($args['status'] )){
            $args['status'] = 1;
            if($_SESSION['reference']['status_page_profile'] !== null){
                $args['status'] = $_SESSION['reference']['status_page_profile'];
            }
        }

        // default session param for this controller
        if (!isset($_SESSION['reference'])) {
            $_SESSION['reference'] = [
                "orderby" => 'created_at',
                "sort"    => "desc"
            ];
        }

        //Reload SESSION variables for user's references
        $ref = new ReferenceModel();
        $ref_model = $ref->newInstance();
        $_SESSION['user']['references_count'] = $ref_model->where('user_id', $_SESSION['user']['id'])->get()->count();
        $_SESSION['user']['references'] = $ref_model->where('user_id', $_SESSION['user']['id'])->get();

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
        $_SESSION['reference']['pagination'] = 15;
        $_SESSION['reference']['status_page_profile'] = $args['status'];

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
                $model->where('status', '=', $args['status']);
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
            'status_page'   => $args['status']
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
}