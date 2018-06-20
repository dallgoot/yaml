<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'tests_ref.php';

use Dallgoot\Yaml\Loader as Loader;

$folder = __DIR__."/../references/";

$files = array_diff(scandir($folder), ['..', '.']);

$yamlLoader = new Loader(null, Loader::EXCEPTIONS_PARSING, 0);

try{
    foreach ($files as $key => $fileName) {
        echo "\n\033[32m$fileName\033[0m";
        $result = $yamlLoader->load($folder.$fileName)->parse();
        $s = json_encode($result);
        // $s = json_decode($e);
        // $s = serialize($result);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg(), 1);
        }
        echo "\n $s";//exit();
        // if ($references[$fileName] !== $json) throw new Exception("\nError Processing $filename : \n$json", 1);
    }
} catch(Error $e) {
    // echo $e->message;
    var_dump($e);
}