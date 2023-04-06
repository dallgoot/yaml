<?php

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Types\Compact;

$yaml = new YamlObject(0);

$o = new \stdClass;

$o->key = 'a';

$yaml->key1 = new Compact([1,2,$o]);

return $yaml;