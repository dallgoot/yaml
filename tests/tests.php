<?php
require_once __DIR__ . '/../vendor/autoload.php';
include 'tests_ref.php';

use Dallgoot\Yaml\Yaml as Y;

$folder = __DIR__."/cases/examples/";

$files = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);

// $yamlLoader = new Loader(null, Loader::EXCEPTIONS_PARSING, 0);
$out = 0;
try {
    $reference = get_object_vars(Y::parseFile(__DIR__.'/definitions/examples_tests.yml'));
    // var_dump($result));exit();
    foreach ($files as $key => $fileInfo) {
        $name = $fileInfo->getFilename();
        $testName = str_replace('.yml','',$name);
        $result = Y::parseFile($fileInfo->getPathname());
        // $content = file_get_contents($fileInfo->getPathname());
        // $result = Y::parse($content);
        $s = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR);
        if (!in_array(json_last_error(), [JSON_ERROR_INF_OR_NAN, JSON_ERROR_NONE ])) {
            throw new Exception(json_last_error_msg()." on $name", 1);
        }
        if ($s === $reference[$testName]) {
            echo "\n\033[32m".(is_array($result) ? $result[0]->getComment(1) : $result->getComment(1))."\033[0m";
        } else {
            echo "\n\033[33m$name\033[0m\n";
            echo $s;
            echo "\n\033[33mSHOULD BE:\n";
            echo $reference[$testName];
            $out = 1;
        }
    }
} catch (Exception |Error $e) {
    var_dump($e);
    $out = 1;
}
exit($out);
