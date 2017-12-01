<?php

namespace GLPI\Telemetry;

class Project
{
    private $name;
    private $slug;
    private $url;
    private $schema_usage;
    private $schema_plugins = true;
    private $mapping = [];
    private $logger;
    private $project_path;
    private $templates_path;
    private $enable_contact = true;
    private $footer_links = [
        'GLPI project'  => [
            'faclass'   => 'fa fa-globe',
            'url'       => 'http://glpi-project.org'
        ],
        'Plugins'       => [
            'faclass'   => 'fa fa-puzzle-piece',
            'url'       => 'http://plugins.glpi-project.org'
        ],
        'Forum'         => [
            'faclass'   => 'fa fa-comments-o',
            'url'       => 'http://forum.glpi-project.org'
        ],
        'Suggest'       => [
            'faclass'   => 'fa fa-lightbulb-o',
            'url'       => 'htt//suggest.glpi-project.org'
        ]
    ];
    private $dyn_references = [
        'num_assets'    => [
            'label'         => 'Number of assets',
            'short_label'   => '# assets'
        ],
        'num_helpdesk'  => [
            'label'         => 'Number of helpdesk',
            'short_label'   => '# helpdesk'
        ]
     ];
    private $dashboard = [
       'nb_telemetry_entries' => true,
       'nb_reference_entries' => true,
       'php_versions'         => true,
       'glpi_versions'        => true,
       'top_plugins'          => true,
       'os_family'            => true,
       'default_languages'    => true,
       'db_engines'           => true,
       'web_engines'          => true,
       'install_modes'        => true,
       'references_countries' => true
    ];

    /**
     * Constructor
     *
     * @param string $name   Project name
     * @param mixed  $logger Logger
     */
    public function __construct($name, $logger = null)
    {
        $this->logger = $logger;
        $this->name = $name;
        $this->slug = strtolower(
            trim(
                preg_replace(
                    '~[^0-9a-z]+~i',
                    '-',
                    preg_replace(
                        '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                        '$1',
                        htmlentities(
                            $name,
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    )
                ),
                ' '
            )
        );
        $this->project_path = __DIR__ . '/../projects/' . $this->slug;
        $this->templates_path =  $this->project_path . '/Templates';
    }

    /**
     * Set project configuration
     *
     * @param array $config Configuration values
     *
     * @return Project
     */
    public function setConfig($config)
    {
        $this->checkConfig($config);

        if (isset($config['url'])) {
            $this->url = $config['url'];
        }

        if (isset($config['enable_contact'])) {
            $this->enable_contact = (bool)$config['enable_contact'];
        }

        if (isset($config['schema'])) {
            $this->setSchemaConfig($config['schema']);
        }

        if (isset($config['mapping'])) {
            $this->mapping = $config['mapping'];
        }

        if (isset($config['footer_links'])) {
            $this->footer_links = $config['footer_links'];
        }

        if (isset($config['dyn_references'])) {
            $this->dyn_references = $config['dyn_references'];
        }

        if (isset($config['dashboard'])) {
            $this->dashboard = array_merge(
                $this->dashboard,
                $config['dashboard']
            );
        }

        return $this;
    }

    /**
     * Check for required options in configuration
     *
     * @param array $config Configuration values
     *
     * @return boolean
     */
    public function checkConfig(array $config)
    {
        if (isset($config['schema']['usage'])) {
            $cusage = $config['schema']['usage'];
            if (!is_array($cusage) && false !== $cusage) {
                throw new \UnexpectedValueException('Schema usage must be an array or false if present!');
            } elseif (is_array($cusage)) {
                if (!isset($config['mapping'])) {
                    throw new \DomainException('a mapping is mandatory if you define schema usage');
                } else {
                    $ukeys = array_keys($cusage);
                    sort($ukeys);
                    $mkeys = array_keys($config['mapping']);
                    sort($mkeys);
                    if ($ukeys != $mkeys) {
                        throw new \UnexpectedValueException('schema usage and mapping keys must fit');
                    }
                }
            }
        }

        if (isset($config['schema']['plugins'])) {
            if (false !== $config['schema']['plugins']) {
                throw new \UnexpectedValueException('Schema plugins must be false if present!');
            }
        }

        if (isset($config['dyn_references'])) {
            if (!is_array($config['dyn_references']) && $config['dyn_references'] !== false) {
                throw new \UnexpectedValueException('Dynamic references configuration must be an array or false');
            }
        }
    }

    /**
     * Set schema configuration
     *
     * @param mixed $config Schema configuration
     *
     * @return void
     */
    private function setSchemaConfig($config)
    {
        if (isset($config['usage'])) {
            if (is_array($config['usage']) || false === $config['usage']) {
                $this->schema_usage = $config['usage'];
            }
        }
        if (isset($config['plugins'])) {
            if (false === $config['plugins']) {
                $this->schema_plugins = false;
            }
        }
    }

    /**
     * Generate or retrieve project's schema
     *
     * @param Zend\Cache\Storage\Adapter\AbstractAdapter|null $cache Cache instance
     *
     * @return json
     */
    public function getSchema($cache)
    {
        if (null != $cache && $cache->hasItem('schema')) {
            $schema = $cache->getItem($this->getSlug() . '_schema.json');
            if (null != $schema) {
                return $schema;
            }
        }

        $jsonfile = realpath(__DIR__ . '/../misc/json.spec.base');
        $schema = json_decode(file_get_contents($jsonfile));

        $schema->id = ($url = $this->getURL()) ? $url : $schema->id;

        $data = $schema->properties->data;
        $slug = $this->getSlug();
        $data->properties->$slug = clone $data->properties->project;
        foreach ($data->required as &$required) {
            if ($required == 'project') {
                $required = $slug;
            }
        }
        unset($data->properties->project);

        $not_required = [];
        //false means no plugins requested
        if (false === $this->schema_plugins) {
            unset($data->properties->$slug->properties->plugins);
            $not_required[] = 'plugins';
        }

        if (null !== $this->schema_usage) {
            //first, drop existing usage config
            unset($data->properties->$slug->properties->usage);
            //false means no usage requested
            if (false !== $this->schema_usage) {
                $usages = new \stdClass();
                $usages->properties = new \stdClass();
                $requireds = [];
                foreach ($this->schema_usage as $usage => $conf) {
                    $object = new \stdClass;
                    $object->type = $conf['type'];
                    if ($conf['required']) {
                        $requireds[] = $usage;
                    }
                    $usages->properties->$usage = $object;
                }
                if (count($requireds)) {
                    $usages->required = $requireds;
                }
                $usages->type = 'object';
                $data->properties->$slug->properties->usage = $usages;
            } else {
                $not_required[] = 'usage';
            }
        }

        if (count($not_required) > 0) {
            $requireds = $data->properties->$slug->required;
            foreach ($requireds as $key => $value) {
                if (in_array($value, $not_required)) {
                    unset($requireds[$key]);
                }
            }
            $data->properties->$slug->required = array_values($requireds);
        }

        $schema = json_encode($schema);

        if (null != $cache) {
            $cache->setItem($this->getSlug() . '_schema.json', $schema);
        }

        return $schema;
    }

    /**
     * Map schema data into model
     *
     * @param array $json JSON sent data as array
     *
     * @return array
     */
    public function mapModel($json)
    {
        $slug = $this->getSlug();

        //basic mapping
        $data = [
            'glpi_uuid' => $this->truncate($json[$slug]['uuid'], 41),
            'glpi_version' => $this->truncate($json[$slug]['version'], 25),
            'glpi_default_language' => $this->truncate($json[$slug]['default_language'], 10),
            'install_mode' => $this->truncate($json[$slug]['install_mode'], 50),
            'db_engine' => $this->truncate($json['system']['db']['engine'], 50),
            'db_version' => $this->truncate($json['system']['db']['version'], 50),
            'db_size' => (int) $json['system']['db']['size'],
            'db_log_size' => (int) $json['system']['db']['log_size'],
            'db_sql_mode' => $json['system']['db']['sql_mode'],
            'web_engine' => $this->truncate($json['system']['web_server']['engine'], 50),
            'web_version' => $this->truncate($json['system']['web_server']['version'], 50),
            'php_version' => $this->truncate($json['system']['php']['version'], 50),
            'php_modules' => implode(',', $json['system']['php']['modules']),
            'php_config_max_execution_time' => (int) $json['system']['php']['setup']['max_execution_time'],
            'php_config_memory_limit' => $this->truncate($json['system']['php']['setup']['memory_limit'], 10),
            'php_config_post_max_size' => $this->truncate($json['system']['php']['setup']['post_max_size'], 10),
            'php_config_safe_mode' => (bool) $json['system']['php']['setup']['safe_mode'],
            'php_config_session' => $json['system']['php']['setup']['session'],
            'php_config_upload_max_filesize' => $this->truncate($json['system']['php']['setup']['upload_max_filesize'], 10),
            'os_family' => $this->truncate($json['system']['os']['family'], 50),
            'os_distribution' => $this->truncate($json['system']['os']['distribution'], 50),
            'os_version' => $this->truncate($json['system']['os']['version'], 50),
        ];

        $usage = [];
        //mapping from schema configuration for usage
        if (null !== $this->schema_usage) {
            if (false !== $this->schema_usage) {
                foreach ($this->mapping as $local => $origin) {
                    $usage[$origin] = $this->truncate($json[$slug]['usage'][$local], 25);
                }
            }
        } else {
            $usage = [
                'glpi_avg_entities' => $this->truncate($json[$slug]['usage']['avg_entities'], 50),
                'glpi_avg_computers' => $this->truncate($json[$slug]['usage']['avg_computers'], 50),
                'glpi_avg_networkequipments' => $this->truncate($json[$slug]['usage']['avg_networkequipments'], 50),
                'glpi_avg_tickets' => $this->truncate($json[$slug]['usage']['avg_tickets'], 25),
                'glpi_avg_problems' => $this->truncate($json[$slug]['usage']['avg_problems'], 25),
                'glpi_avg_changes' => $this->truncate($json[$slug]['usage']['avg_changes'], 25),
                'glpi_avg_projects' => $this->truncate($json[$slug]['usage']['avg_projects'], 25),
                'glpi_avg_users' => $this->truncate($json[$slug]['usage']['avg_users'], 25),
                'glpi_avg_groups' => $this->truncate($json[$slug]['usage']['avg_groups'], 25),
                'glpi_ldap_enabled' => (bool) $json[$slug]['usage']['ldap_enabled'],
                // 'glpi_smtp_enabled' => (bool) $json[$slug]['usage']['smtp_enabled'],
                'glpi_mailcollector_enabled' => (bool) $json[$slug]['usage']['mailcollector_enabled']
            ];
        }

        return $data + $usage;
    }

    /**
     * Truncate a string
     *
     * @param string  $string Original string to truncate
     * @param integer $length String length limit
     *
     * @return string
     */
    public function truncate($string, $length)
    {
        if (mb_strlen($string) > $length) {
            if ($this->logger !== null) {
                $this->logger->warning("String exceed length $length", $string);
            } else {
                trigger_error("String exceed length $length\n$string", E_USER_NOTICE);
            }
            $string = mb_substr($string, 0, $length);
        }

        return $string;
    }

    /**
     * Get template path for project
     *
     * @return array
     */
    public function getTemplatesPath()
    {
        if (file_exists($this->templates_path)) {
            return [$this->templates_path];
        }
        return [];
    }

    /**
     * Get path for template
     *
     * @param string $tpl Template name
     *
     * @return string
     */
    public function pathFor($tpl)
    {
        $slug = 'default';
        if (file_exists($this->templates_path . '/' . $this->getSlug() . '/' . $tpl)) {
            $slug = $this->getSlug();
        }
        return $slug . '/' . $tpl;
    }

    /**
     * Get project name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get project slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get project URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Does project provide a logo
     *
     * @return boolean
     */
    public function getLogo()
    {
        $file = __DIR__ . '/../public/pics/logo.png';
        if (file_exists($this->project_path . '/logo.png')) {
            $file = $this->project_path . '/logo.png';
        }
        return file_get_contents($file);
    }

    /**
     * Get JSON example data
     *
     * @return string
     */
    public function getExampleData()
    {
        $file = __DIR__ . '/../misc/json-glpi.spec';
        if (file_exists($this->project_path . '/json-' . $this->getSlug() . '.spec')) {
            $file = $this->project_path . '/json-' . $this->getSlug() . '.spec';
        }
        return file_get_contents($file);
    }

    /**
     * Is contact page active for current project
     *
     * @return boolean
     */
    public function hasContactPage()
    {
        return $this->enable_contact;
    }

    /**
     * Get footer links
     *
     * @return array
     */
    public function getFooterLinks()
    {
        return $this->footer_links;
    }

    /**
     * Get dynamic references
     *
     * @return array|false
     */
    public function getDynamicReferences()
    {
        return $this->dyn_references;
    }

    /**
     * Get dashboard configuration
     *
     * @return array
     */
    public function getDashboardConfig()
    {
        if (false === $this->schema_plugins) {
            $this->dashboard['top_plugins'] = false;
        }
        return $this->dashboard;
    }
}
