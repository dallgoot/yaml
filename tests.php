<?php
include 'tests_ref.php';
include 'YamlLoader.php';

$folder = "./examples/";

$files = array_diff(scandir($folder), array('..', '.'));

$yamlLoader = new YamlLoader();

try{
	foreach ($files as $key => $fileName) {
		$result = $yamlLoader->load($folder.$fileName)->parse();
		$json = json_encode($result);
		$s = serialize($result);
		echo "\n $s";exit();
		// if ($references[$fileName] !== $json) throw new Exception("\nError Processing $filename : \n$json", 1);
	}

}catch(Exception $e)
{
	// echo $e->message;
	var_dump($e);
}