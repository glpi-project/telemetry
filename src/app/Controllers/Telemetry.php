<?php namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use App\Models\Telemetry  as TelemetryModel;
use App\Models\GlpiPlugin as GlpiPluginModel;


class Telemetry  extends ControllerAbstract {
   public function view(Request $request, Response $response) {
      $plugins = GlpiPluginModel::all()->toJson();
      $this->container->logger->addInfo($plugins);
      $this->render('telemetry.html');
   }
}
