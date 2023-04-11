<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Yaml;

$yamlContent = <<<EOF
compact_object: {a: 1, b: 2, c: OK}

compact_array: [0,1,2,OK]
EOF;

$obj = Yaml::parse($yamlContent);

//printing specifically some values
print_r($obj->compact_object->c);
print_r($obj->compact_array[3]);

//modifying those same values
$obj->compact_object->c = 3;
$obj->compact_array[3] = 3;

//printing the corresponding YAML
print_r(Yaml::dump($obj));