<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'tests_ref.php';

use Dallgoot\Yaml\Loader as Loader;

$folder = __DIR__."/../references/";

$files = array_diff(scandir($folder), ['..', '.']);

$yamlLoader = new Loader(null, Loader::EXCEPTIONS_PARSING, 0);

try {
    foreach ($files as $key => $fileName) {
        echo "\n\033[32m$fileName\033[0m";
        $result = $yamlLoader->load($folder.$fileName)->parse();
        $s = json_encode($result, 512);
        if (!in_array(json_last_error(), [JSON_ERROR_INF_OR_NAN, JSON_ERROR_NONE ])) {
            throw new Exception(json_last_error_msg()." on $fileName", 1);
        }
        echo "\n $s";
    }
    exit(0);
} catch (Exception |Error $e) {
    var_dump($e);
    exit(1);
}
