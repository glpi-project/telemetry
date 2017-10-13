Set up a PROJECT
================

By default, the Telemetry app is designed to work along with [GLPI](http://glpi-project.org); but it is popssible to tune a bit the JSON schema in order to fit other projects. This can be achieved using the `config.inc.php` file.

In the `$config` array; there is a `project` key you can customize. Here is an example for the [Galette](https://galette.eu) project:

```php
<?php

return $config = [
    'project' => [
        'name'   => 'Galette',
        'url'    => 'https://galette.eu',
        'schema' => [
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
    //...
];
```

What you can see here, is that the project name and URL have been customized. Both thos values will be used to generate the JSON schema file we validate against. You can review the generated schema file using `http://telemetry.yoursite.com/telemetry/schema.json`.

The `schema` key permit to make changes in the schema itself. For now, you can disable `plugins` (if your project do not have plugins) and you can disable or change `usage` (the average counts). To disable one of those two properties, just the the key to false (`'usage' => false`).

If you provide an array into the `usage` key; its keys will become the properties names in the schema; and you can define their types and if they're mandatory or not. Refer to the [JSON Schema](http://json-schema.org/) specification if you want to know more.

The actual database was designed using GLPI fields; and names are hardcoded. So, if you change `usage` keys; you'll have to provide a mapping beetween your key and the existing one in the databasebase. This is the goal of the `mapping` entry in the configuration.

When working on schema configuration, make sure to tunr debug to true for the application; generated schema would be cached otherwise, and your changes will not be visible :)
