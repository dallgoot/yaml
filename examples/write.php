<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Yaml;

//Getting some document as PHP variable $YamlObject
//the document here is a PHP file used for tests
$testName = 'yamlObject_properties';
$yamlObject = (include "tests/cases/dumping/$testName.php");

//transform $yamlObject to YAML
$text = Yaml::dump($yamlObject, 0);

//getting the tests results
$nameResultPair = get_object_vars(Yaml::parseFile('tests/definitions/dumping_tests.yml'));

//verify that the text(yaml) we got is the same as we expected for this test
if ($nameResultPair[$testName] === $text) {
    echo 'WRITE OK !!!';
} else {
    print_r('EXPECTED', $nameResultPair[$testName]);
    print_r('RECEIVED', $text);
}
