<?php
namespace Dallgoot\Yaml;

$yaml  = new YamlObject(0);
$yaml1 = new YamlObject(0);
$yaml2 = new YamlObject(0);


$yaml[0] = [1,2,3];

$yaml1->a = 1;

$yaml2->b = 2;

return [$yaml, $yaml1, $yaml2];