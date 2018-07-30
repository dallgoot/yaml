<?php
require_once __DIR__ . '/vendor/autoload.php';

use \Dallgoot\Yaml as Y;

/* USE CASE 1
* load and parse if file exists
*/
// $yaml = Y::loadFile('./dummy.yml');//->parse();
$yaml = Y::parseFile('./references/Example 2.12.yml', null, 2);
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
var_dump(Y::parse($a));
var_dump(Y::parse($b));

// USE CASE 3
// $yamlObjList = [];
// $yloader = new Loader();
// foreach(['file1', 'file2', 'file3'] as $key => $fileName)
// {
//     $yamlObjList[] =  $yloader->load($fileName)->parse();
// }
