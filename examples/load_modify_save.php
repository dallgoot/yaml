<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Yaml;

//loading YAML as YamlObject
$yamlObject = Yaml::parseFile('./examples/dummy.yml', $options = null, $debug = null);
//modifying some part
$yamlObject->object->array[3]->integer = '123456789';
//dumping the corresponding YAML
print_r(Yaml::dump($yamlObject, 0));