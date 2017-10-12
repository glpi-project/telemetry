<?php

namespace App;

class Project
{
    private $name;
    private $slug;
    private $url;
    private $schema_usage;

    /**
     * Constructor
     *
     * @param string $name Project name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->slug = strtolower(
            trim(
                preg_replace(
                    '/[^A-Za-z0-9-]+/',
                    '-',
                    $name
                )
            )
        );
    }

    /**
     * Set project configuration
     *
     * @param arra $config Configuration values
     *
     * @return Project
     */
    public function setConfig($config)
    {
        if (isset($config['url'])) {
            $this->url = $config['url'];
        }

        if (isset($config['schema'])) {
            $this->setSchemaConfig($config['schema']);
        }

        return $this;
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
            } else {
                throw new \UnexpectedValueException('Schema usage must be an array or false if present!');
            }
        }
        if (isset($config['plugins'])) {
            if (false === $config['plugins']) {
                $this->schema_plugins = false;
            } else {
                throw new \UnexpectedValueException('Schema plugins must be false if present!');
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
                $requireds = [];
                foreach ($this->schema_usage as $usage => $conf) {
                    $object = new \stdClass;
                    $object->type = $conf['type'];
                    if ($conf['required']) {
                        $requireds[] = $usage;
                    }
                    $data->properties->$slug->properties->usage->properties->$usage = $object;
                }
                if (count($requireds)) {
                    $data->properties->$slug->properties->usage->required = $requireds;
                }
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
            reset($requireds);
            $data->properties->$slug->required = $requireds;
        }

        $schema = json_encode($schema);

        if (null != $cache) {
            $cache->setItem($this->getSlug() . '_schema.json', $schema);
        }

        return $schema;
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
}
