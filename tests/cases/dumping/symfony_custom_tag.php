<?php

use Dallgoot\Yaml\Types\Tagged;
use Dallgoot\Yaml\Types\Compact;

$object = new Compact();
$object->foo = 'bar';


return new Tagged('!php/object', $object);