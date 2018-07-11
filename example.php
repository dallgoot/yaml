<?php
require_once __DIR__ . '/vendor/autoload.php';

use \Dallgoot\Yaml\Loader;

/* USE CASE 1
* load and parse if file exists
*/
// $yaml = (new Loader('./references/Example 2.10.yml', null,2))->parse();
$yaml = (new Loader('./dummy.yml', null, 2))->parse();
var_dump($yaml);
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
var_dump((new Loader(null, Loader::EXCEPTIONS_PARSING))->parse($a));
var_dump((new Loader(null, Loader::EXCEPTIONS_PARSING))->parse($b));

// USE CASE 3
// $yamlObjList = [];
// $yloader = new Loader();
// foreach(['file1', 'file2', 'file3'] as $key => $fileName)
// {
//     $yamlObjList[] =  $yloader->load($fileName)->parse();
// }
