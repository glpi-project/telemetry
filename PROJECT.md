Set up a project
================

By default, the Telemetry app is designed to work along with [GLPI](http://glpi-project.org); but it is possible to tune it a bit in order to fit other projects.

JSON schema
-----------

JSON schema and some mappings can be overrided using the `config.inc.php` file

In the `$config` array; there is a `project` key you can customize. Here is an example for the [Galette](https://galette.eu) project:

```php
<?php

return $config = [
    'project' => [
        'name'   => 'Galette',
        'url'    => 'https://galette.eu',
        'enable_contact' => false,
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

What you can see here, is that the project name and URL have been customized. Both those values will be used to generate the JSON schema file we validate against. You can review the generated schema file using `http://telemetry.yoursite.com/telemetry/schema.json`.

The `schema` key permit to make changes in the schema itself. For now, you can disable `plugins` (if your project do not have plugins) and you can disable or change `usage` (the average counts). To disable one of those two properties, just the the key to false (`'usage' => false`).

If you provide an array into the `usage` key; its keys will become the properties names in the schema; and you can define their types and if they're mandatory or not. Refer to the [JSON Schema](http://json-schema.org/) specification if you want to know more.

The actual database was designed using GLPI fields; and names are hardcoded. So, if you change `usage` keys; you'll have to provide a mapping beetween your key and the existing one in the databasebase. This is the goal of the `mapping` entry in the configuration.

When working on schema configuration, make sure to turn debug to true for the application; generated schema would be cached otherwise, and your changes will not be visible :)

Finally, note that the `enable_contact` set to false will entirely disable the embed contact page.

User Interface
--------------

Some elements on the user interface may be tuned too. This currently includes:
1- projects logo,
2- footer links,
3- templates contents.

Parts of the configuration uses the main configuration file (`config.inc.php`); other rely on the presence of some files in the `projects/{project_slug}` directory (where `{project_slug}` is... Your project slug :)).

### Logo

If you want to display your own logo, just create it as `projects/{project_slug}/logo.png`. Even if the file extension is `PNG`, this should work with any image type.

No controls are done on the logo file; the application will just read it from the filesystem if the file exists.

### Dashboard

It is not possible yet to customize dashboard on a per configuration basis; but you can disable some featues you do no use. In order to achive that, add a `dashboard` entry to your project's configuration array and set to false unused entries.

```php
return $config = [
    'project' => [
        'name'   => 'My Project',
        'dashboard' => [
            'install_modes' => false,
            'references_countries' => false
        ],
    ],
    //...
```

On the above example, the map with countries from referencs and the installation modes part will not be shown.

### Footer links

You may want to provide footers links related to your onw project. Use the `project/footer_links` parameter of the `config.inc.php` file. Each link can be tuned using several properties. Let's take an example, see the following configuration:

```php
return $config = [
    'project' => [
        'name'   => 'My Project',
        'footer_links' => [
            'Main website'   => [
                'faclass'   => 'fa fa-globe',
                'url'       => 'https://my-project.org'
            ],
            'Documentation' => [
                'faclass'   => 'fa fa-book',
                'url'       => 'https://doc.my-project.org/'
            ],
            'Forums'         => [
                'faclass'   => 'fa fa-comments-o',
                'url'       => 'https://forums.my-project.org'
            ],
        ],
    ],
    //...
```

This defines 3 different links in the application footer: the main website, project's documentation and forums. The array key will become the dispayed text in the link. The url parameter is the link itself. Both are mandatory.
The `faclass` parameter is optionnal. When defined, a `<i>` tag will be prepend to the displayed text using the provided class. Refer to [FontAwesome](http://fontawesome.io/) to get the class you want.

### Templates contents

Telemetry application relies on [Twig](https://twig.symfony.com/) to display HTML contents to user. Defaults templates are stored in the `app/Templates/default` directory.

Each of those files can be overrided per project. Just create you own file under `projects/[project_slug}/Templates/{project_slug}` directory and it will be used :)

The easiest way to go is to inherit from the original default file; and only override parts you want. An example `projects/[project_slug}/Templates/{project_slug}/telemetry.html.twig` file should looks like:

```twig
{{ '{% extends "default/telemetry.html.twig" '}}%}

{{ '{% block header '}}%}
{{ "{% set header_text = 'Since PROJECT x.y, we collect anonymous
             <a id='register' href='#' data-toggle='modal' data-target='#json_data_example'>data</a> from instance of voluntary users.'
"}}%}
{{ '{{ parent() ' }}}}
{{ '{% endblock '}}%}

{{ '{% block content '}}%}
{{ "{% set versionchart_text = '<i class='fa fa-exclamation-circle'></i> we don&apos;t have any data for versions prior to x.y' "}}%}
{{ '{{ parent() ' }}}}
{{ '{% endblock '}}%}
```

### Dynamic references

In the references pages, along with traditionnal informations (company, referent, ...); you can add your own related data; such as estimations of main object used by your application. For something like GLPI, we'll want to know the number of assets and helpdesk entries. For something like Galette, we'll be interested in number of members...

In order to achieve that; you can add a `references` key in the config file:
```php
return $config = [
    'project' => [
        'name'   => 'My Project',
        'references' => [
            'num_whatuwant' => [
                'label'         => 'Number of what you want',
                'short_label'   => '#wyw'
            ]
        ]
        //[...]
    ]
];
```

This will add a dynamic reference `num_whatuwant` (will be used in the database)  with the long label `Number of what you want` (used in fom) and the sort label `#wyw` (used in array list).
Doing so implies a table named `{project_slug}_references` exists. See INSTALL.md to know how to configure your own migration files.
