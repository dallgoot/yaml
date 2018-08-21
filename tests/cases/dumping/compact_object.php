<?php

$yaml = new YamlObject;


$o = new StdClass;

$o->key1 = 'a';
$o->key2 = 'b';

$yaml->key1 = Compact::wrap($o);

return $yaml;