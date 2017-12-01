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
            ['TEST', ['schema' => ['usage' => false]], 'test', null, [], 22],
            [
                'Schema usage',
                [
                    'schema'    => ['usage' => ['avg_test']],
                    'mapping'   => ['avg_test' => 'glpi_avg_entities']
                ],
                'schema-usage',
                null,
                ['glpi_avg_entities'],
                23
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
    public function testGetInfos($name, $config, $slug, $url, $map, $mapcount = 33)
    {
        $json = [
            $slug       => [
                'uuid'              => '',
                'version'           => '',
                'default_language'  => '',
                'install_mode'      => ''
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

    /**
     * Data provider for schemas
     *
     * @return array
     */
    protected function schemasProvider()
    {
        return [
            [
                'GLPI',
                [
                    'name'  => 'GLPI',
                    'url'   => 'http://glpi-project.org'
                ],
                'json-glpi.spec.schema'
            ], [
                'Galette',
                [
                    'name'      => 'Galette',
                    'url'       => 'https://galette.eu',
                    'schema'    => [
                        'usage' => [
                            'avg_members'        => [
                                'type'      => 'string',
                                'required'  => true
                            ],
                            'avg_contributions'  => [
                                'type'      => 'string',
                                'required'  => true
                            ],
                            'avg_transactions'   => [
                                'type'      => 'string',
                                'required'  => true
                            ]
                        ]
                    ],
                    'mapping' => [
                        'avg_members'       => 'avg_entities',
                        'avg_contributions' => 'avg_computers',
                        'avg_transactions'  => 'avg_networkequipments'
                    ]
                ],
                'json-galette.spec.schema'
            ], [
                'No plugins',
                [
                    'name'      => 'No plugins',
                    'schema'    => [
                        'plugins' => false
                    ]
                ],
                'json-noplugins.spec.schema'
            ], [
                'No usage',
                [
                    'name'      => 'No usage',
                    'schema'    => [
                        'usage' => false
                    ]
                ],
                'json-nousage.spec.schema'
            ]
        ];
    }

    /**
     * Test project schema generation
     *
     * @dataProvider schemasProvider
     *
     * @param string $name     Project name
     * @param array  $config   Project configuration
     * @param string $filename Expected file result
     *
     * @return void
     */
    public function testDynamicSchema($name, $config, $filename)
    {
        $filepath = __DIR__ . '/../../../../schemas/' . $filename;
        $this->boolean(file_exists($filepath))->isTrue();
        $schema = file_get_contents($filepath);
        $schema = json_decode($schema);

        $this
            ->if($this->newTestedInstance($name))
            ->then
                ->object($this->testedInstance->setConfig($config))
                    ->isInstanceOf(get_class($this->testedInstance))
                ->object(json_decode($this->testedInstance->getSchema(null)))
                    ->isEqualTo($schema)
        ;
    }

    /**
     * Provider for truncate tests
     *
     * @return array
     */
    protected function truncateProvider()
    {
        return [
            [
                'A simple string', 50, false
            ], [
                'A simple string', 10, 'A simple s'
            ], [
                'éè', 1, 'é'
            ]
        ];
    }

    /**
     * Test truncate
     *
     * @dataProvider truncateProvider
     *
     * @param string       $string   String to truncate
     * @param integer      $length   Maximal lenght
     * @param string|false $expected Expected return. False means no change (and no error)
     *
     * @return void
     */
    public function testTruncate($string, $length, $expected)
    {
        $this->newTestedInstance('test');
        $instance = $this->testedInstance;

        if ($expected === false) {
            $this->string($this->testedInstance->truncate($string, $length))
                ->isIdenticalTo($expected === false ? $string : $expected);
        } else {
            $this->when(
                function () use ($instance, $string, $length, $expected) {
                    $this->string($instance->truncate($string, $length))
                        ->isIdenticalTo($expected);
                }
            )->error()
                ->withMessage("String exceed length $length\n$string")
                ->exists();
        }
    }

    /**
     * Test getTemplatesPath
     *
     * @return void
     */
    public function testGetTemplatesPath()
    {
        $this->assert('getTemplatesPath')
            ->given($this->newTestedInstance('test'))
                ->if($this->function->file_exists = false)
                    ->then
                        ->array($this->testedInstance->getTemplatesPath())
                            ->isIdenticalTo([])
                ->if($this->function->file_exists = true)
                    ->then
                        ->array($this->testedInstance->getTemplatesPath())
                            ->isIdenticalTo(
                                [realpath(__DIR__ . '/../../../../../app/') .
                                '/../projects/' . $this->testedInstance->getSlug() . '/Templates']
                            )
        ;
    }

    /**
     * Test pathFor
     *
     * @return void
     */
    public function testPathFor()
    {
        $this->assert('pathFor')
            ->given($this->newTestedInstance('test'))
                ->if($this->function->file_exists = false)
                    ->then
                        ->string($this->testedInstance->pathFor('page.html.twig'))
                            ->isIdenticalTo('default/page.html.twig')
                ->if($this->function->file_exists = true)
                    ->then
                        ->string($this->testedInstance->pathFor('page.html.twig'))
                            ->isIdenticalTo($this->testedInstance->getSlug() . '/page.html.twig')
        ;
    }

    /**
     * Test getLogo
     *
     * @return void
     */
    public function testGetLogo()
    {
        $this->assert('pathFor')
            ->given($this->newTestedInstance('test'))
                ->if($this->function->file_get_contents = function ($file) {
                    return $file;
                })
                    ->then
                        ->if($this->function->file_exists = false)
                            ->then
                                ->string($this->testedInstance->getLogo())
                                    ->isIdenticalTo(
                                        realpath(__DIR__ . '/../../../../../app/') .
                                        '/../public/pics/logo.png'
                                    )
                        ->if($this->function->file_exists = true)
                            ->then
                                ->string($this->testedInstance->getLogo())
                                    ->isIdenticalTo(
                                        realpath(__DIR__ . '/../../../../../app/') .
                                        '/../projects/'.$this->testedInstance->getSlug().'/logo.png'
                                    )
        ;
    }

    /**
     * Test hasContactPage
     *
     * @return void
     */
    public function testHasContactPage()
    {
        $this
            ->if($this->newTestedInstance('test'))
            ->then
                ->object($this->testedInstance->setConfig([
                    'name' => 'test'
                ]))
                    ->isInstanceOf(get_class($this->testedInstance))
                        ->boolean($this->testedInstance->hasContactPage())
                            ->isTrue()
                ->object($this->testedInstance->setConfig([
                    'name'              => 'test',
                    'enable_contact'    => true
                ]))
                    ->isInstanceOf(get_class($this->testedInstance))
                        ->boolean($this->testedInstance->hasContactPage())
                            ->isTrue()
                ->object($this->testedInstance->setConfig([
                    'name'              => 'test',
                    'enable_contact'    => false
                ]))
                    ->isInstanceOf(get_class($this->testedInstance))
                        ->boolean($this->testedInstance->hasContactPage())
                            ->isFalse()
        ;
    }

    /**
     * Test footer links
     *
     * @return void
     */
    public function testGetFooterLinks()
    {
         $this
            ->if($this->newTestedInstance('test'))
            ->then
                ->object($this->testedInstance->setConfig([
                    'name' => 'test'
                ]))
                ->array($this->testedInstance->getFooterLinks())
                    ->hasSize(4)
                    ->hasKeys(['GLPI project', 'Plugins', 'Forum', 'Suggest'])
                ->object($this->testedInstance->setConfig([
                    'name'          => 'test',
                    'footer_links'  => [
                        'one'   => [],
                        'two'   => []
                    ]
                ]))
                ->array($this->testedInstance->getFooterLinks())
                    ->hasSize(2)
                    ->hasKeys(['one', 'two'])
        ;
    }
}
