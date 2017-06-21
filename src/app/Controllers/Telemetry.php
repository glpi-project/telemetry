<?php namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use App\Models\Telemetry  as TelemetryModel;
use App\Models\GlpiPlugin as GlpiPluginModel;
use App\Models\TelemetryGlpiPlugin;


class Telemetry  extends ControllerAbstract {

   public function view(Request $request, Response $response) {
      //$plugins = GlpiPluginModel::all()->toJson();
      $this->render('telemetry.html');

      return $response;
   }

   public function send(Request $request, Response $response) {
      $ctype     = $request->getHeader('Content-Type');
      $json = $request->getParsedBody();

      if (strpos('application/json', $ctype[0]) === false) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'Content-Type must be application/json'
            ]);
      }

      if (!is_array($json)) {
         return $response
            ->withStatus(400)
            ->withJson([
               'message' => 'body seems invalid (not a json ?)'
            ]);
      }

      $json = $json['data'];

      $data = [
         'glpi_uuid'                      => $json['glpi']['uuid'],
         'glpi_version'                   => $json['glpi']['version'],
         'glpi_default_language'          => $json['glpi']['default_language'],
         'glpi_avg_entities'              => $json['glpi']['usage']['avg_entities'],
         'glpi_avg_computers'             => $json['glpi']['usage']['avg_computers'],
         'glpi_avg_networkequipments'     => $json['glpi']['usage']['avg_networkequipments'],
         'glpi_avg_tickets'               => $json['glpi']['usage']['avg_tickets'],
         'glpi_avg_problems'              => $json['glpi']['usage']['avg_problems'],
         'glpi_avg_changes'               => $json['glpi']['usage']['avg_changes'],
         'glpi_avg_projects'              => $json['glpi']['usage']['avg_projects'],
         'glpi_avg_users'                 => $json['glpi']['usage']['avg_users'],
         'glpi_avg_groups'                => $json['glpi']['usage']['avg_groups'],
         'glpi_ldap_enabled'              => (bool) $json['glpi']['usage']['ldap_enabled'],
         'glpi_smtp_enabled'              => (bool) $json['glpi']['usage']['smtp_enabled'],
         'glpi_mailcollector_enabled'     => (bool) $json['glpi']['usage']['mailcollector_enabled'],
         'db_engine'                      => $json['system']['db']['engine'],
         'db_version'                     => $json['system']['db']['version'],
         'db_size'                        => (int) $json['system']['db']['size'],
         'db_log_size'                    => (int) $json['system']['db']['log_size'],
         'db_sql_mode'                    => $json['system']['db']['sql_mode'],
         'php_version'                    => $json['system']['php']['version'],
         'php_modules'                    => implode(',', $json['system']['php']['modules']),
         'php_config_max_execution_time'  => (int) $json['system']['php']['setup']['max_execution_time'],
         'php_config_memory_limit'        => $json['system']['php']['setup']['memory_limit'],
         'php_config_post_max_size'       => $json['system']['php']['setup']['post_max_size'],
         'php_config_safe_mode'           => (bool) $json['system']['php']['setup']['safe_mode'],
         'php_config_session'             => $json['system']['php']['setup']['session'],
         'php_config_upload_max_filesize' => $json['system']['php']['setup']['upload_max_filesize'],
         'os_family'                      => $json['system']['os']['family'],
         'os_distribution'                => $json['system']['os']['distribution'],
         'os_version'                     => $json['system']['os']['version'],
      ];

      $telemetry_m = TelemetryModel::create($data);

      $this->container->logger->warning('telemetry_m', (array) $telemetry_m);


      // manage plugins
      foreach ($json['glpi']['plugins'] as $plugin) {
         $plugin_m = GlpiPluginModel::firstOrCreate(['pkey' => $plugin['key']]);

         TelemetryGlpiPlugin::create([
            'telemetry_entry_id' => $telemetry_m->id,
            'glpi_plugin_id'     => $plugin_m->id,
            'version'            => $plugin['version']
         ]);

      }

      return $response;
   }
}
