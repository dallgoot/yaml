<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml;

$yamlContent = <<<EOF
anchor_definition: &anchor_name OK

anchor_call: *anchor_name
EOF;

$obj = Yaml::parse($yamlContent);

var_dump($obj);

var_dump($obj->anchor_definition);
var_dump($obj->anchor_call);

//change anchor refernce value
$obj->addReference('anchor_name', 123);
var_dump($obj);

var_dump($obj->anchor_definition);
var_dump($obj->anchor_call);

//change one anchor to new value
$obj->anchor_definition = "abc";

var_dump($obj->anchor_definition);
var_dump($obj->anchor_call);
