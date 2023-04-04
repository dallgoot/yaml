<?php

use Dallgoot\Yaml\Types\YamlObject;

$yaml = new YamlObject(0);

$yaml->positiveFloat = 0.5353;
$yaml->negativeFloat = -2.65;

$yaml->castedPositiveFloat = (float) 2.0;
$yaml->castedNegativeFloat = (float) -2;

$yaml->positiveExponentFloat =  2.3e4;
$yaml->negativeExponentFloat =  2.3e-3;

$yaml->positiveInfinity = INF;
$yaml->negativeInfinity = -INF;

$yaml->notANumber = NAN;//this has the PHP type 'double'


return $yaml;