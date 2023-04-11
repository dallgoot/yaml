<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dallgoot\Yaml\Yaml;

/**
 * Testing/Debugging Loader
 *
 * use as follows : "php examples/testing.php DEBUG_LEVEL YAML_FILENAME"
 */
const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

$debug = (int) (isset($argv[1]) ? $argv[1] : null);
$file = (string) (isset($argv[2]) ? $argv[2] : null);
echo memory_get_usage() . "\n";
/* USE CASE 1
* load and parse if file exists
*/
$content = file_get_contents($file);
$yaml = Yaml::parse($content, 0, $debug);

echo memory_get_usage() . "\n";

print_r(json_encode($yaml, JSON_OPTIONS));