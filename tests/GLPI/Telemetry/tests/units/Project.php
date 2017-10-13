<?php

namespace GLPI\Telemetry\test\units;

use \atoum;

/**
 * Test Project class
 */
class Project extends atoum
{
    /**
     * Data provider for basic infos
     *
     * @return array
     */
    protected function infosProvider()
    {
        return [
            ['GLPI', [], 'glpi', null, ['glpi_avg_computers']],
            ['Any Project', ['url' => 'http://perdu.com'], 'any-project', 'http://perdu.com', ['glpi_avg_computers']],
            ['é_è', [], 'e-e', null, ['glpi_avg_computers']],
            ['TEST', ['schema' => ['usage' => false]], 'test', null, [], 21],
            [
                'Schema usage',
                [
                    'schema'    => ['usage' => ['avg_test']],
                    'mapping'   => ['avg_test' => 'glpi_avg_entities']
                ],
                'schema-usage',
                null,
                ['glpi_avg_entities'],
                22
            ]
        ];
    }

    /**
     * Test project global informations
     *
     * @dataProvider infosProvider
     *
     * @param string  $name     Project name
     * @param array   $config   Project configuration
     * @param string  $slug     Expected slug
     * @param string  $url      Expected URL
     * @param string  $map      Expected extra mapping
     * @param integer $mapcount Expected count
     *
     * @return void
     */
    public function testGetInfos($name, $config, $slug, $url, $map, $mapcount = 32)
    {
        $json = [
            $slug       => [
                'uuid'              => '',
                'version'           => '',
                'default_language'  => ''
            ],
            'system'    => [
                'db'            => [
                    'engine'    => '',
                    'version'   => '',
                    'size'      => '',
                    'log_size'  => '',
                    'sql_mode'  => ''
                ],
                'web_server'    => [
                    'engine'    => '',
                    'version'   => ''
                ],
                'php'           => [
                    'version'               => '',
                    'modules'               => [],
                    'setup'                 => [
                        'max_execution_time'    => '',
                        'memory_limit'          => '',
                        'post_max_size'         => '',
                        'safe_mode'             => '',
                        'session'               => '',
                        'upload_max_filesize'   => ''
                    ]
                ],
                'os'            => [
                    'family'        => '',
                    'distribution'  => '',
                    'version'       => ''
                ]
            ]
        ];

        if (!isset($config['schema']['usage'])) {
            $json[$slug]['usage'] = [
                'avg_entities'          => '',
                'avg_computers'         => '',
                'avg_networkequipments' => '',
                'avg_tickets'           => '',
                'avg_problems'          => '',
                'avg_changes'           => '',
                'avg_projects'          => '',
                'avg_users'             => '',
                'avg_groups'            => '',
                'ldap_enabled'          => '',
                'mailcollector_enabled' => ''
            ];
        } elseif (isset($config['mapping'])) {
            $json[$slug]['usage'] = [];
            foreach (array_keys($config['mapping']) as $key) {
                $json[$slug]['usage'][$key] = '';
            }
        }

        $this
            ->if($this->newTestedInstance($name))
            ->then
                ->object($this->testedInstance->setConfig($config))
                    ->isInstanceOf(get_class($this->testedInstance))
                ->string($this->testedInstance->getName())
                    ->isIdenticalTo($name)
                ->string($this->testedInstance->getSlug())
                    ->isIdenticalTo($slug)
                ->variable($this->testedInstance->getURL())
                    ->isIdenticalTo($url)
                ->array($this->testedInstance->mapModel($json))
                    ->hasSize($mapcount)
                    ->hasKeys(array_merge(['glpi_uuid'], $map))
        ;
    }

    /**
     * Test configuration errors
     *
     * @return void
     */
    public function testConfErrors()
    {
        $this
            ->if($inst = $this->newTestedInstance('TEST'))
                ->exception(
                    function () use ($inst) {
                        $inst->setConfig([
                            'schema' => [
                                'usage' => 'a string'
                            ]
                        ]);
                    }
                )
                    ->isInstanceOf('UnexpectedValueException')
                    ->hasMessage('Schema usage must be an array or false if present!')
                ->exception(
                    function () use ($inst) {
                        $inst->setConfig([
                            'schema' => [
                                'plugins' => ['some' => 'conf']
                            ]
                        ]);
                    }
                )
                    ->isInstanceOf('UnexpectedValueException')
                    ->hasMessage('Schema plugins must be false if present!')
                ->exception(
                    function () use ($inst) {
                        $inst->setConfig([
                            'schema' => [
                                'usage' => ['test' => ['type' => 'string']]
                            ]
                        ]);
                    }
                )
                    ->isInstanceOf('DomainException')
                    ->hasMessage('a mapping is mandatory if you define schema usage')
                ->exception(
                    function () use ($inst) {
                        $inst->setConfig([
                            'schema' => [
                                'usage'     => ['test' => ['type' => 'string']],
                            ],
                            'mapping'   => ['atest' => 'avalue']
                        ]);
                    }
                )
                    ->isInstanceOf('UnexpectedValueException')
                    ->hasMessage('schema usage and mapping keys must fit')
        ;
    }
}
