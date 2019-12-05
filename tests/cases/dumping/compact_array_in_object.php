<?php

namespace Dallgoot\Yaml;


$yaml = new YamlObject(0);

$o = new \StdClass;

$o->array = [1,2,3];

$yaml->key1 = new Compact($o);

return $yaml;