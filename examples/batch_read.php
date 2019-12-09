<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Loader;


// USE CASE 3
$yamlObjList = [];
$yloader = new Loader(null, 0, $debug);
foreach(['examples/dummy.yml', 'examples/config.yml'] as $key => $fileName)
{
    $yamlObjList[] =  $yloader->load($fileName)->parse();
}

var_dump($yamlObjList);
