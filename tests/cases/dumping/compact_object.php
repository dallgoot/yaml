<?php

namespace Dallgoot\Yaml;

$yaml = new YamlObject(0);


$o = new \StdClass;

$o->key1 = 'a';
$o->key2 = 'b';

$yaml->key1 = new Compact($o);

return $yaml;