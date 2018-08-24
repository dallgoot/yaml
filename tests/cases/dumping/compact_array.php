<?php

namespace Dallgoot\Yaml;


$o = new \StdClass;

$o->key1 = Compact::wrap([1,2,3]);


return $o;