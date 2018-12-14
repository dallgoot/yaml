<?php
require_once __DIR__.'/vendor/autoload.php';

use \Dallgoot\Yaml\Yaml as Y;

/**
 * Display some use cases for Yaml library
 */
const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

/* USE CASE 1
* load and parse if file exists
*/
ini_set("auto_detect_line_endings", 1);
$content = file_get_contents('./tests/cases/parsing/multiline_quoted_with_blank.yml');//var_dump($content);
// $content = file_get_contents('./tests/cases/examples/Example_2_17.yml');//var_dump($content);
$yaml = Y::parse($content, null, 2);
// $yaml = Y::parseFile('./references/Example 2.27.yml', null, 1);
var_dump($yaml);
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
var_dump(Y::parse($a));
var_dump(Y::parse($b));

// USE CASE 3
// $yamlObjList = [];
// $yloader = new Loader();
// foreach(['file1', 'file2', 'file3'] as $key => $fileName)
// {
//     $yamlObjList[] =  $yloader->load($fileName)->parse();
// }
