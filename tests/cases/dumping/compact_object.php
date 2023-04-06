<?php

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Types\Compact;


$yaml = new YamlObject(0);


$o = new \stdClass;

$o->key1 = 'a';
$o->key2 = 'b';

$yaml->key1 = new Compact($o);

return $yaml;