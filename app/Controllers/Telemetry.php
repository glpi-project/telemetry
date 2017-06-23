<?php namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Database\Capsule\Manager as DB;

use App\Models\Telemetry  as TelemetryModel;
use App\Models\GlpiPlugin as GlpiPluginModel;
use App\Models\TelemetryGlpiPlugin;


class Telemetry  extends ControllerAbstract {

   public function view(Request $request, Response $response) {
      // retrieve php versions
      $php_versions = TelemetryModel::select(
            DB::raw("split_part(php_version, '.', 1) || '.' || split_part(php_version, '.', 2) as version,
                     count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '1 YEAR'"))
         ->groupBy(DB::raw("version"))
         ->get()
         ->toArray();

      // retrieve glpi versions
      $glpi_versions = TelemetryModel::select(
            DB::raw("glpi_version as version, count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '1 YEAR'"))
         ->groupBy(DB::raw("glpi_version"))
         ->get()
         ->toArray();

      // retrieve top 5 plugins
      $top_plugins = GlpiPluginModel::join( 'telemetry_glpi_plugin',
                                            'glpi_plugin.id', '=', 'telemetry_glpi_plugin.glpi_plugin_id')
         ->select(DB::raw("glpi_plugin.pkey, count(telemetry_glpi_plugin.*) as total"))
         ->where('telemetry_glpi_plugin.created_at', '>=', DB::raw("NOW() - INTERVAL '1 YEAR'"))
         ->orderBy('total', 'desc')
         ->limit(5)
         ->groupBy(DB::raw("glpi_plugin.pkey"))
         ->get()
         ->toArray();

      // retrieve os
      $os_family = TelemetryModel::select(
            DB::raw("os_family, count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '1 YEAR'"))
         ->groupBy(DB::raw("os_family"))
         ->get()
         ->toArray();

      $this->render('telemetry.html', [
         'php_versions' => json_encode([
            'labels' => array_column($php_versions, 'version'),
            'series' => array_column($php_versions, 'total')
         ]),
         'glpi_versions' => json_encode([
            'labels' => array_column($glpi_versions, 'version'),
            'series' => array_column($glpi_versions, 'total')
         ]),
         'top_plugins' => json_encode([
            'labels' => array_column($top_plugins, 'pkey'),
            'series' => array_column($top_plugins, 'total')
         ]),
         'os_family' => json_encode([
            'labels' => array_column($os_family, 'os_family'),
            'series' => array_column($os_family, 'total')
         ]),
         'json_data_example' => $this->container['json_spec']
      ]);

      return $response;
   }

   public function send(Request $request, Response $response) {
      $json    = $request->getParsedBody()['data'];

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
         // 'glpi_smtp_enabled'              => (bool) $json['glpi']['usage']['smtp_enabled'],
         'glpi_mailcollector_enabled'     => (bool) $json['glpi']['usage']['mailcollector_enabled'],
         'db_engine'                      => $json['system']['db']['engine'],
         'db_version'                     => $json['system']['db']['version'],
         'db_size'                        => (int) $json['system']['db']['size'],
         'db_log_size'                    => (int) $json['system']['db']['log_size'],
         'db_sql_mode'                    => $json['system']['db']['sql_mode'],
         'web_engine'                      => $json['system']['web_server']['engine'],
         'web_version'                     => $json['system']['web_server']['version'],
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


      // manage plugins
      foreach ($json['glpi']['plugins'] as $plugin) {
         $plugin_m = GlpiPluginModel::firstOrCreate(['pkey' => $plugin['key']]);

         TelemetryGlpiPlugin::create([
            'telemetry_entry_id' => $telemetry_m->id,
            'glpi_plugin_id'     => $plugin_m->id,
            'version'            => $plugin['version']
         ]);

      }

      return $response
         ->withJson([
            'message' => 'OK'
         ]);
   }
}
