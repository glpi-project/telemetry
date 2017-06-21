<?php namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use App\Models\Telemetry  as TelemetryModel;
use App\Models\GlpiPlugin as GlpiPluginModel;


class Telemetry  extends ControllerAbstract {

   public function view(Request $request, Response $response) {
      //$plugins = GlpiPluginModel::all()->toJson();
      $this->render('telemetry.html');

      return $response;
   }

   public function send(Request $request, Response $response) {
      $ctype     = $request->getHeader('Content-Type');
      $json_send = $request->getParsedBody();

      $this->container->logger->warning('json_send', (array) $json_send);

      if (strpos('application/json', $ctype[0]) === false) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'Content-Type must be application/json'
            ]);
      }

      if (!is_array($json_send)) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'body seems invalid (not a json ?)'
            ]);
      }

      return $response;
   }
}
