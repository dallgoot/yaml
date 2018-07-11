<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'tests_ref.php';

use Dallgoot\Yaml\Loader as Loader;

$folder = __DIR__."/../references/";

$files = array_diff(scandir($folder), ['..', '.']);

$yamlLoader = new Loader(null, Loader::EXCEPTIONS_PARSING, 0);

try {
    foreach ($files as $key => $fileName) {
        $result = $yamlLoader->load($folder.$fileName)->parse();
        $s = json_encode($result, 512);
        if (!in_array(json_last_error(), [JSON_ERROR_INF_OR_NAN, JSON_ERROR_NONE ])) {
            throw new Exception(json_last_error_msg()." on $fileName", 1);
        }
        if ($s === $references[$fileName]) {
            echo "\n\033[32m$fileName\033[0m";
        } else {
            echo "\n\033[33m$fileName\033[0m";
            echo "\n$s";
            echo "\n\033[33mSHOULD BE:";
            echo "\n\033[33m".$references[$fileName]."\033[0m";
        }
    }
    exit(0);
} catch (Exception |Error $e) {
    var_dump($e);
    exit(1);
}
