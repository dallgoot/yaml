<?php
require_once __DIR__ . '/vendor/autoload.php';

use \Dallgoot\Yaml\Loader;

/* USE CASE 1
* load and parse if file exists
*/
$yaml = (new Loader('./references/Example 2.11.yml', null, 3))->parse();
// $yaml = (new Loader('./dummy.yml', null, 2))->parse();
var_dump($yaml);

// USE CASE 2
// $yaml = (new Loader())->parse('SOME YAML STRING');

// USE CASE 3
// $yamlObjList = [];
// $yloader = new Loader();
// foreach(['file1', 'file2', 'file3'] as $key => $fileName)
// {
//     $yamlObjList[] =  $yloader->load($fileName)->parse();
// }
