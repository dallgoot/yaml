<?php
define('PROJECT_ROOT', __DIR__."/../");

require_once PROJECT_ROOT . 'vendor/autoload.php';

use Dallgoot\Yaml;


$testName = 'yamlObject_properties';
$yamlObject = (include PROJECT_ROOT . "tests/cases/dumping/$testName.php");


$text = Yaml::dump($yamlObject, 0);


$nameResultPair = get_object_vars(/** @scrutinizer ignore-type */ Yaml::parseFile(PROJECT_ROOT . 'tests/definitions/dumping_tests.yml'));


if ($nameResultPair[$testName] === $text) {
    echo 'WRITE OK !!!';
} else {
    var_dump('EXPECTED', $nameResultPair[$testName]);
    var_dump('RECEIVED', $text);
}
