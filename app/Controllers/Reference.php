<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\DynamicReference;
use Illuminate\Pagination\LengthAwarePaginator;
use Slim\Http\Request;
use Slim\Http\Response;

class Reference extends ControllerAbstract
{

    public function view(Request $req, Response $res)
    {
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
        $_SESSION['reference']['pagination'] = 15;

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
                $order_table = (in_array($order_field, $dyn_refs) ? $join_table : 'reference');
                // retrieve data from model
                $model = ReferenceModel::newInstance();
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
                $model->where('is_displayed', '=', true);
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

        $references->setPath($this->container->get('settings')['baseurl']."reference");

        // render in twig view
        $this->render($this->container->project->pathFor('reference.html.twig'), [
            'total'         => ReferenceModel::active()->count(),
            'class'         => 'reference',
            'showmodal'     => isset($get['showmodal']),
            'uuid'          => isset($get['uuid']) ? $get['uuid'] : '',
            'references'    => $references,
            'pagination'    => $references->appends($_GET)->render(),
            'orderby'       => $_SESSION['reference']['orderby'],
            'sort'          => $_SESSION['reference']['sort'],
            'dyn_refs'      => $dyn_refs
        ]);
    }

    public function register(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        // clean data
        unset($post['g-recaptcha-response']);
        unset($post['csrf_name']);
        unset($post['csrf_value']);

        $ref_data = $post;
        $dyn_data = [];

        $dyn_ref = $this->container->project->getDynamicReferences();
        if (false !== $dyn_ref) {
            foreach (array_keys($dyn_ref) as $ref) {
                if (isset($post[$ref])) {
                    $dyn_data[$ref] = (int)$post[$ref];
                    unset($ref_data[$ref]);
                }
            }
        }

        // alter data
        $ref_data['country'] = strtolower($ref_data['country']);

        // create reference in db
        if ('' == $ref_data['uuid']) {
            $reference = ReferenceModel::create(
                $ref_data
            );
        } else {
            $reference = ReferenceModel::updateOrCreate(
                ['uuid' => $ref_data['uuid']],
                $ref_data
            );
        }

        if (false !== $dyn_ref) {
            $dynamics = DynamicReference::newInstance();
            $dynamics->setTable($this->container->project->getSlug() . '_reference');

            $exists = $dynamics->where('reference_id', $reference['id'])->get();

            if (0 === $exists->count()) {
                $dyn_data['reference_id'] = $reference['id'];
                $dynamics->insert(
                    $dyn_data
                );
            } else {
                $dynamics
                    ->where('reference_id', '=', $reference['id'])
                    ->update($dyn_data);
            }
        }

        // send a mail to admin
        $mail = new \PHPMailer;
        $mail->setFrom($this->container['settings']['mail_from']);
        $mail->addAddress($this->container['settings']['mail_admin']);
        $mail->Subject = "A new reference has been submitted: ".$post['name'];
        $mail->Body    = var_export($post, true);
        $mail->send();

        // store a message for user (displayed after redirect)
        $this->container->flash->addMessage(
            'success',
            'Your reference has been stored! An administrator will moderate it before display on the site.'
        );

        // redirect to ok page
        return $res->withRedirect($this->container->router->pathFor('reference'));
    }
}
