<?php
require_once __DIR__.'/vendor/autoload.php';

use \Dallgoot\Yaml;

/**
 * Display some use cases for Yaml library
 */
const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

$debug = (int) (isset($argv[1]) ? $argv[1] : null);

/* USE CASE 1
* load and parse if file exists
*/
// $content = file_get_contents('./tests/cases/parsing/multidoc_mapping.yml');//var_dump($content);
$content = file_get_contents('./tests/definitions/examples_tests.yml');//var_dump($content);
$yaml = Yaml::parse($content, 0, $debug);
// var_dump($yaml);
var_dump(json_encode($yaml, JSON_OPTIONS));
exit(0);

// USE CASE 2
$a = <<<EOF
sequence:
    - string_key: 1
EOF;
$b = <<<EOF
#2
mapping:
    string_key: 1
EOF;
var_dump(Yaml::parse($a, 0, $debug));
var_dump(Yaml::parse($b, 0, $debug));

// USE CASE 3
// $yamlObjList = [];
// $yloader = new Loader(null, 0, $debug);
// foreach(['file1', 'file2', 'file3'] as $key => $fileName)
// {
//     $yamlObjList[] =  $yloader->load($fileName)->parse();
// }
