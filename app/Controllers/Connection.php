<?php namespace GLPI\Telemetry\Controllers;

use GLPI\Telemetry\Controllers\ControllerAbstract;
use Slim\Http\Request;
use Slim\Http\Response;
use GLPI\Telemetry\Models\Authentication as AuthenticationModel;

class Connection extends ControllerAbstract
{
    public function view()
    {
        $this->render($this->container->project->pathFor('connection.html.twig'), [
         'class' => 'connection'
        ]);
    }

    public function send(Request $req, Response $res)
    {
        $post = $req->getParsedBody();
        
        $auth_ref = new AuthenticationModel;
        $auth = $auth_ref->newInstance();

        if($auth->Authenticate($post)) {
       		// store a message for user (displayed after redirect)
       		$msg_text = "You are now connected !";
	        $this->container->flash->addMessage(
	            'success',
	            $msg_text
	        );
	       	$this->container->flash->addMessage(
	            'success',
	            'Welcome '.$post['username']
	        );


	       	$this->setUserSession([
                    'username' => $post['username'],
                    'user_info' => $auth->getUserInfo()
                ]);

	        //redirect
        	return $res->withRedirect($this->container->router->pathFor('telemetry'));

        } else {
       		// store a message for user (displayed after redirect)
	        $this->container->flash->addMessage(
	            'warn',
	            'Wrong username or password'
	        );
	        //redirect
	        return $res->withRedirect($this->container->router->pathFor('connection'));

        }

    }

    public function setUserSession($tab)
    {
    	$_SESSION['user']['id'] = $tab['user_info']['id'];
        $_SESSION['user']['username'] = $tab['username'];
        $_SESSION['user']['is_admin'] = $tab['user_info']['is_admin'];
        $_SESSION['user']['mail'] = $tab['user_info']['mail'];
        $_SESSION['user']['references'] = $tab['user_info']['references_info'];
        $_SESSION['user']['references_count'] = $tab['user_info']['references_count'];
    }

    public function disconnect(Request $req, Response $res)
    {
    	unset($_SESSION['user']);
    	return $res->withRedirect($this->container->router->pathFor('telemetry'));
    }
}
