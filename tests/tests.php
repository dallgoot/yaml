<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'tests_ref.php';

use Dallgoot\Yaml\Loader as Loader;

$folder = __DIR__."/../references/";

$files = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);

$yamlLoader = new Loader(null, Loader::EXCEPTIONS_PARSING, 0);
$out = 0;
try {
    foreach ($files as $key => $fileName) {
        $name = basename($fileName);
        $result = $yamlLoader->load($fileName)->parse();
        $s = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR);
        if (!in_array(json_last_error(), [JSON_ERROR_INF_OR_NAN, JSON_ERROR_NONE ])) {
            throw new Exception(json_last_error_msg()." on $name", 1);
        }
        if ($s === $references[$name]) {
            echo "\n\033[32m$name\033[0m";
        } else {
            echo "\n\033[33m$name\033[0m\n";
            echo $s;
            echo "\n\033[33mSHOULD BE:\n";
            echo $references[$name];
            $out = 1;
        }
    }
} catch (Exception |Error $e) {
    var_dump($e);
    $out = 1;
}
exit($out);
