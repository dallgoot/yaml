<?php

namespace Dallgoot\Yaml;

$yaml = new YamlObject;

$o = new \Stdclass;

$o->key = 'a';

$yaml->key1 = new Compact([1,2,$o]);

return $yaml;