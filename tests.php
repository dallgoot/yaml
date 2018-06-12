<?php
include 'tests_ref.php';
require_once __DIR__ . '/vendor/autoload.php';

$folder = "./examples/";

$files = array_diff(scandir($folder), ['..', '.']);

$yamlLoader = new Dallgoot\Yaml\Loader();

try{
	foreach ($files as $key => $fileName) {
		echo "\n\033[32m$fileName\033[0m";
		$result = $yamlLoader->load($folder.$fileName)->parse();
		$s = json_encode($result);
		// $s = json_decode($e);
		// $s = serialize($result);
		echo "\n $s";//exit();
		// if ($references[$fileName] !== $json) throw new Exception("\nError Processing $filename : \n$json", 1);
	}

}catch(Error $e)
{
	// echo $e->message;
	var_dump($e);
}