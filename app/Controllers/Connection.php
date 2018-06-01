<?php
namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use GLPI\Telemetry\Middleware\CsrfView;
use Slim\Http\Request;
use Slim\Http\Response;
use GLPI\Telemetry\Models\Authentication as AuthenticationModel;

class Connection extends ControllerAbstract
{
    public function view(Request $req, Response $res)
    {
        $params = ['ajax' => $req->isXhr()];

        if ($req->isXhr()) {
            $slimGuard = $this->container['csrf'];
            $slimGuard->validateStorage();
            // Generate new tokens
            $csrfNameKey = $slimGuard->getTokenNameKey();
            $csrfValueKey = $slimGuard->getTokenValueKey();
            $keyPair = $slimGuard->generateToken();

            $params += [
                'class'    => 'popup-connection',
                'my_csrf'  => [
                    'name'    => $keyPair['csrf_name'],
                    'value'   => $keyPair['csrf_value']
                ]
            ];
        }
        $this->render(
            $this->container->project->pathFor('connection.html.twig'),
            $params
        );
    }

    public function send(Request $req, Response $res, $redirect = 'telemetry')
    {
        $post = $req->getParsedBody();

        $auth_ref = new AuthenticationModel;
        $auth = $auth_ref->newInstance();

        if ($auth->authenticate($post)) {
            $_SESSION['user'] = $auth->getUser()['attributes'];

            //redirect
            return $res->withRedirect($this->container->router->pathFor($redirect));
        } else {
            // store a message for user (displayed after redirect)
            $this->container->flash->addMessage(
                'warn',
                'Wrong username or password'
            );
            //redirect
            return $res->withRedirect($this->container->router->pathFor('telemetry'));
        }
    }

    public function disconnect(Request $req, Response $res)
    {
        unset($_SESSION['user']);
        return $res->withRedirect($this->container->router->pathFor('telemetry'));
    }
}
