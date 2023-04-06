<?php

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Types\Compact;


$yaml = new YamlObject(0);

$o = new \stdClass;

$o->array = [1,2,3];

$yaml->key1 = new Compact($o);

return $yaml;