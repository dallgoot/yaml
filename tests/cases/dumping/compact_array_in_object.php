<?php

$yaml = new YamlObject;

$o = new StdClass;

$o->array = [1,2,3];

$yaml->key1 = Compact::wrap($o);

return $yaml;