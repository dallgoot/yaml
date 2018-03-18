<?php
include 'YamlLoader.php';

$folder = "./examples/";

$files = array_diff(scandir($folder), array('..', '.'));

$yamlLoader = new \YamlLoader\YamlLoader();

try{
	foreach ($files as $key => $fileName) {
		$yamlLoader->load($folder.$fileName)->parse();
	}
}catch(Exception $e)
{
	// echo $e->message;
	var_dump($e);
}