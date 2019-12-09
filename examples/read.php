<?php
require_once __DIR__.'/../vendor/autoload.php';

use \Dallgoot\Yaml;

$debug = 0;

/* USE CASE 1
* load and parse if file exists
*/
$yaml = Yaml::parseFile('./examples/dummy.yml', 0, $debug);

var_dump($yaml->object->array[0]);

$yamlContent = <<<EOF
--- some document we don't care about
# below the document we want
---
- ignore_me
- mapping:
    somekey:
        array:
            - OK
EOF;

var_dump(Yaml::parse($yamlContent)[1][1]->mapping->somekey->array[0]);