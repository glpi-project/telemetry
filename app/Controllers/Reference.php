<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\PageAbstract;
use GLPI\Telemetry\Models\Reference as ReferenceModel;
use GLPI\Telemetry\Models\DynamicReference;
use Illuminate\Pagination\LengthAwarePaginator;
use Slim\Http\Request;
use Slim\Http\Response;

class Reference extends PageAbstract
{

    public function view(Request $req, Response $res, array $args)
    {
        $get = $req->getQueryParams();

        $_SESSION['reference'] = $this->setDifferentsFilters($get, $args, false);

        $refs_tab = $this->load_refs(false, ReferenceModel::ACCEPTED);
        $references = $refs_tab['references'];
        $dyn_refs = $refs_tab['dyn_refs'];

        $references->setPath($this->container->router->pathFor('reference'));

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
            'dyn_refs'      => $dyn_refs,
            'user'          => $_SESSION['user']
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
        if ($_SESSION['user'] != null) {
            $ref_data['user_id'] = $_SESSION['user']['id'];
        } else {
            $ref_data['user_id'] = null;
        }

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
            $dref = new DynamicReference();
            $dynamics = $dref->newInstance();
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

    public function update(Request $req, Response $res)
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
        $ref_data['status'] = 1;
        if ($_SESSION['user'] != null) {
            $ref_data['user_id'] = $_SESSION['user']['id'];
        } else {
            $ref_data['user_id'] = null;
        }

        //ref
        $reference = ReferenceModel::updateOrCreate(
            ['id' => $ref_data['id']],
            $ref_data
        );

        $ref = new ReferenceModel();
        $model = $ref->newInstance();
        $model->updateStatus($reference['id'], 1);

        //dynamic ref
        $dref = new DynamicReference();
        $dynamics = $dref->newInstance();
        $dynamics->setTable($this->container->project->getSlug() . '_reference');

        $exists = $dynamics->where('reference_id', $reference['id'])->get();

        if (1 === $exists->count()) {
            $dynamics
                ->where('reference_id', '=', $reference['id'])
                ->update($dyn_data);

            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'success',
                'Update done !'
            );
        } else {
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'warn',
                'Can\'t update your reference, please contact an administrator.'
            );
        }

        // redirect to ok page
        return $res->withRedirect($this->container->router->pathFor('profile'));
    }

    public function delete(Request $req, Response $res)
    {
        $post = $req->getParsedBody();

        $dref = new DynamicReference();
        $dynamics = $dref->newInstance();
        $dynamics->setTable($this->container->project->getSlug() . '_reference');
        $dynamics->where('reference_id', $post['ref_id'])->forceDelete();

        $ref = new ReferenceModel();
        $model = $ref->newInstance();
        $model->where('id', $post['ref_id'])->forceDelete();

        // store a message for user (displayed after redirect)
        $this->container->flash->addMessage(
            'success',
            'Successful deletion !'
        );

        // redirect to ok page
        return $res->withRedirect($this->container->router->pathFor('profile'));
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

        return $res->withRedirect($this->container->router->pathFor('reference'));
    }
}
