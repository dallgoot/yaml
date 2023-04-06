<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Yaml;

$yamlContent = <<<EOF
anchor_definition: &anchor_name OK

anchor_call: *anchor_name
EOF;

$obj = Yaml::parse($yamlContent);

echo "values on parsing\n";
print_r($obj->anchor_definition);
print_r($obj->anchor_call);

echo "\nchange anchor/reference value to 123\n";
$obj->addReference('anchor_name', 123);

print_r($obj->anchor_definition);
print_r($obj->anchor_call);

echo "\nchange one anchor to new value 'abc'\n";
$obj->anchor_definition = 'abc';

print_r($obj->anchor_definition);
print_r($obj->anchor_call);

echo "\nunset anchor_call and re-set value\n";
unset($obj->anchor_call);
$obj->anchor_call = 'xyz';

print_r($obj->anchor_definition);
print_r($obj->anchor_call);

echo "\nchange anchor/reference value to 789\n";
$obj->addReference('anchor_name', 789);

print_r($obj->anchor_definition);
print_r($obj->anchor_call);

print_r($obj);