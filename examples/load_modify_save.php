<?php
namespace Dallgoot\Yaml;

require_once __DIR__ . '/../../vendor/autoload.php';

use Dallgoot;

$fileName = __DIR__ . '/../dummy.yml';

$yamlObject = Yaml::parseFile($fileName, $options = null, $debug = null);

// var_dump($yamlObject);
// var_dump($yamlObject->object->array['integer']);
$yamlObject->object->array['integer'] = '"2"';

// var_dump($yamlObject);

$yaml = Yaml::dump($yamlObject, 0);
var_dump($yaml);