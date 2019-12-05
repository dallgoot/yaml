<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use Dallgoot\Yaml\YamlObject;

$yaml = new YamlObject(0);

$o = new \StdClass;

$o->memberOfO = 'some really really really really really really really really really very long text as a simple string';

$yaml->propA = [1,2,3];

$yaml->propB = $o;

return $yaml;