<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Loader;


// array of YAML content per file
$yamlObjList = [];
//loader object
$yloader = new Loader(null, 0, $debug);
//documents to load
$files = ['examples/dummy.yml', 'examples/config.yml'];

foreach($files as $key => $fileName)
{
    $yamlObjList[] =  $yloader->load($fileName)->parse();
}

print_r($yamlObjList);
