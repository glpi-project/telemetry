<?php

use \mageekguy\atoum;

$coverageField = new atoum\report\fields\runner\coverage\html(
    'GLPI Telemetry',
    __DIR__ . '/code-coverage'
);

$coverageField->setRootUrl(
    'file://' . realpath(__DIR__ . '/code-coverage/')
);

$script
    ->addDefaultReport()
    ->addField($coverageField);
