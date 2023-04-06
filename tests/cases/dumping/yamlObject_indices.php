<?php
use Dallgoot\Yaml\Types\YamlObject;

$yaml = new YamlObject(0);

$o = new \stdClass;

$o->memberOfO = 'some really really really really really really really really really very long text as a simple string';

$yaml[0] = [1,2,3];

$yaml[1] = $o;

return $yaml;