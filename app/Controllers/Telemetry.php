<?php namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Database\Capsule\Manager as DB;
use Numbers\Number;

use App\Models\Telemetry  as TelemetryModel;
use App\Models\GlpiPlugin as GlpiPluginModel;
use App\Models\Reference  as ReferenceModel;
use App\Models\TelemetryGlpiPlugin;


class Telemetry  extends ControllerAbstract {

   public function view(Request $request, Response $response) {
      $get   = $request->getQueryParams();
      $years = 99;
      if (isset($get['years']) && $get['years'] != -1) {
         $years = $get['years'];
      }

      // retrieve nb of telemtry entries
      $raw_nb_tel_entries = TelemetryModel
         ::where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->count();
      $nb_tel_entries = [
         'raw' => $raw_nb_tel_entries,
         'nb'  => (string) Number::n($raw_nb_tel_entries)->round(2)->getSuffixNotation()
      ];

     // retrieve nb of reference entries
      $raw_nb_ref_entries = ReferenceModel
         ::where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->count();
      $nb_ref_entries = [
         'raw' => $raw_nb_ref_entries,
         'nb'  => (string) Number::n($raw_nb_ref_entries)->round(2)->getSuffixNotation()
      ];

      // retrieve php versions
      $raw_php_versions = TelemetryModel::select(
            DB::raw("split_part(php_version, '.', 1) || '.' || split_part(php_version, '.', 2) as version,
                     date_trunc('month', created_at) as raw_month_year,
                     to_char(date_trunc('month', created_at), 'YYYY MON') as month_year,
                     count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy(DB::raw("month_year, raw_month_year, version"))
         ->orderBy(DB::raw("raw_month_year"), 'ASC')
         ->get()
         ->toArray();

      // start reconstruct data
      $php_versions = [];
      $php_versions_legend = [];
      $php_versions_labels = [];
      $php_versions_series = [];
      foreach ($raw_php_versions as $data) {
         $php_versions_legend[] = $data['version'];
         $php_versions_labels[] = $data['month_year'];
         $php_versions[$data['version']]
                      [$data['month_year']]
                        = $data['total'];
      }
      // prepare final data
      $php_versions_legend = array_unique($php_versions_legend);
      $php_versions_labels = array_unique($php_versions_labels);
      foreach ($php_versions as $version_name => $version) {
         $x_data = $y_data = [];
         foreach ($php_versions_labels as $month_year) {
            $x_data[] = $month_year;
            if (isset($version[$month_year])) {
               $y_data[] = $version[$month_year];
            } else {
               $y_data[] = 'null';
            }
         }
         $php_versions_series[] = [
            'name' => "PHP ".$version_name,
            'y'    => $y_data,
            'x'    => $x_data,
            'mode' => 'lines+markers',
         ];
      }

      // retrieve avg usage
      // TODO

      // retrieve reference country
      $references_countries = ReferenceModel::select(
            DB::raw("country as cca2, count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy(DB::raw("country"))
         ->orderBy('total', 'desc')
         ->get()
         ->toArray();
      $all_cca2 = array_column($this->container->countries, 'cca2');
      foreach ($references_countries as &$ctry) {
         //replace alpha2 by alpha3 codes
         $cca2 = strtoupper($ctry['cca2']);
         $idx  = array_search($cca2, $all_cca2);
         $ctry['cca3'] = strtolower($this->container->countries[$idx]['cca3']);
         $ctry['name'] = $this->container->countries[$idx]['name']['common'];
         unset($ctry['cca2']);
      }

      // retrieve glpi versions
      $glpi_versions = TelemetryModel::select(
            DB::raw("TRIM(trailing '-dev' FROM glpi_version) as version,
                     count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy(DB::raw("glpi_version"))
         ->get()
         ->toArray();

      // retrieve top 5 plugins
      $top_plugins = GlpiPluginModel::join( 'telemetry_glpi_plugin',
                                            'glpi_plugin.id', '=', 'telemetry_glpi_plugin.glpi_plugin_id')
         ->select(DB::raw("glpi_plugin.pkey, count(telemetry_glpi_plugin.*) as total"))
         ->where('telemetry_glpi_plugin.created_at',
                 '>=',
                 DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->orderBy('total', 'desc')
         ->limit(5)
         ->groupBy(DB::raw("glpi_plugin.pkey"))
         ->get()
         ->toArray();

      // retrieve os
      $os_family = TelemetryModel::select(
            DB::raw("os_family, count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy(DB::raw("os_family"))
         ->get()
         ->toArray();

      // retrieve languages
      $languages = TelemetryModel::select(
            DB::raw("glpi_default_language, count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy(DB::raw("glpi_default_language"))
         ->get()
         ->toArray();

      // retrieve db engine
      $db_engines = TelemetryModel::select(
            DB::raw("CASE
                        WHEN UPPER(db_engine) LIKE 'MARIA%' THEN 'MariaDB'
                        WHEN UPPER(db_engine) LIKE 'MYSQL%' THEN 'MySQL'
                        ELSE 'MySQL'
                     END as reduced_db_engine,
                     count(*) as total")
         )
         ->where('created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'"))
         ->groupBy('reduced_db_engine')
         ->get()
         ->toArray();


      // retrieve web engine
      $web_engines = TelemetryModel::select(
            DB::raw("web_engine, count(*) as total")
         )
         ->where([
            ['created_at', '>=', DB::raw("NOW() - INTERVAL '$years YEAR'")],
            ['web_engine', '<>', '']
         ])
         ->groupBy(DB::raw("web_engine"))
         ->get()
         ->toArray();

      $this->render('telemetry.html', [
         'form' => [
            'years' => $get['years']
         ],
         'class' => 'telemetry',
         'nb_telemetry_entries' => json_encode($nb_tel_entries),
         'nb_reference_entries' => json_encode($nb_ref_entries),
         'php_versions' => json_encode($php_versions_series),
         'glpi_versions' => json_encode([[
            'type'    => 'pie',
            'hole'    => .4,
            'palette' => 'belize11',
            'labels'  => array_column($glpi_versions, 'version'),
            'values'  => array_column($glpi_versions, 'total')
         ]]),
         'top_plugins' => json_encode([[
            'type'   => 'bar',
            'marker' => ['color' => "#22727B"],
            'x'      => array_column($top_plugins, 'pkey'),
            'y'      => array_column($top_plugins, 'total')
         ]]),
         'os_family' => json_encode([[
            'type'    => 'pie',
            'hole'    => .4,
            'palette' => 'fall6',
            'labels'  => array_column($os_family, 'os_family'),
            'values'  => array_column($os_family, 'total')
         ]]),
         'default_languages' => json_encode([[
            'type'    => 'pie',
            'palette' => 'combo',
            'labels'  => array_column($languages, 'glpi_default_language'),
            'values'  => array_column($languages, 'total')
         ]]),
         'db_engines' => json_encode([[
            'type'   => 'pie',
            'hole'   => .4,
            'palette' => 'nivo',
            'labels' => array_column($db_engines, 'reduced_db_engine'),
            'values' => array_column($db_engines, 'total')
         ]]),
         'web_engines' => json_encode([[
            'type'    => 'pie',
            'hole'    => .4,
            'palette' => 'bluestone',
            'labels'  => array_column($web_engines, 'web_engine'),
            'values'  => array_column($web_engines, 'total')
         ]]),
         'references_countries' => json_encode($references_countries),
         'json_data_example' => $this->container['json_spec']
      ]);

      return $response;
   }

   public function send(Request $request, Response $response) {
      $json    = $request->getParsedBody()['data'];

      $data = [
         'glpi_uuid'
            => $this->truncate($json['glpi']['uuid'], 41),
         'glpi_version'
            => $this->truncate($json['glpi']['version'], 25),
         'glpi_default_language'
            => $this->truncate($json['glpi']['default_language'], 10),
         'glpi_avg_entities'
            => $this->truncate($json['glpi']['usage']['avg_entities'], 50),
         'glpi_avg_computers'
            => $this->truncate($json['glpi']['usage']['avg_computers'], 50),
         'glpi_avg_networkequipments'
            => $this->truncate($json['glpi']['usage']['avg_networkequipments'], 50),
         'glpi_avg_tickets'
            => $this->truncate($json['glpi']['usage']['avg_tickets'], 25),
         'glpi_avg_problems'
            => $this->truncate($json['glpi']['usage']['avg_problems'], 25),
         'glpi_avg_changes'
            => $this->truncate($json['glpi']['usage']['avg_changes'], 25),
         'glpi_avg_projects'
            => $this->truncate($json['glpi']['usage']['avg_projects'], 25),
         'glpi_avg_users'
            => $this->truncate($json['glpi']['usage']['avg_users'], 25),
         'glpi_avg_groups'
            => $this->truncate($json['glpi']['usage']['avg_groups'], 25),
         'glpi_ldap_enabled'
            => (bool) $json['glpi']['usage']['ldap_enabled'],
         // 'glpi_smtp_enabled'
         //   => (bool) $json['glpi']['usage']['smtp_enabled'],
         'glpi_mailcollector_enabled'
            => (bool) $json['glpi']['usage']['mailcollector_enabled'],
         'db_engine'
            => $this->truncate($json['system']['db']['engine'], 50),
         'db_version'
            => $this->truncate($json['system']['db']['version'], 50),
         'db_size'
            => (int) $json['system']['db']['size'],
         'db_log_size'
            => (int) $json['system']['db']['log_size'],
         'db_sql_mode'
            => $json['system']['db']['sql_mode'],
         'web_engine'
            => $this->truncate($json['system']['web_server']['engine'], 50),
         'web_version'
            => $this->truncate($json['system']['web_server']['version'], 50),
         'php_version'
            => $this->truncate($json['system']['php']['version'], 50),
         'php_modules'
            => implode(',', $json['system']['php']['modules']),
         'php_config_max_execution_time'
            => (int) $json['system']['php']['setup']['max_execution_time'],
         'php_config_memory_limit'
            => $this->truncate($json['system']['php']['setup']['memory_limit'], 10),
         'php_config_post_max_size'
            => $this->truncate($json['system']['php']['setup']['post_max_size'], 10),
         'php_config_safe_mode'
            => (bool) $json['system']['php']['setup']['safe_mode'],
         'php_config_session'
            => $json['system']['php']['setup']['session'],
         'php_config_upload_max_filesize'
            => $this->truncate($json['system']['php']['setup']['upload_max_filesize'], 10),
         'os_family'
            => $this->truncate($json['system']['os']['family'], 50),
         'os_distribution'
            => $this->truncate($json['system']['os']['distribution'], 50),
         'os_version'
            => $this->truncate($json['system']['os']['version'], 50),
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

   public function geojson(Request $request, Response $response) {
      $dir = $this->container->countries_dir;
      $countries_geo = [];
      foreach (scandir("$dir/data/") as $file) {
         if (strpos($file, '.geo.json') !== false) {
            $geo_alpha3 = str_replace('.geo.json', '', $file);
            $countries_geo[$geo_alpha3] = json_decode(file_get_contents("$dir/data/$file"), true);
         }
      }

      return $response->withStatus(200)
         ->withHeader('Content-Type', 'application/json')
         ->write(json_encode($countries_geo));
   }

   private function truncate($string, $length) {
      if (strlen($string) > $length) {
         $this->container->log->warning("String exceed length $length", $string);
         $string = substr($string, 0, $length);
      }

      return $string;
   }
}
