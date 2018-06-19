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
			switch (json_last_error()) {
		        case JSON_ERROR_DEPTH: $msg = 'Maximum stack depth exceeded';break;
		        case JSON_ERROR_STATE_MISMATCH: $msg = ' Underflow or the modes mismatch';break;
		        case JSON_ERROR_CTRL_CHAR: $msg = ' Unexpected control character found';break;
		        case JSON_ERROR_SYNTAX: $msg = ' Syntax error, malformed JSON';break;
		        case JSON_ERROR_UTF8: $msg = ' Malformed UTF-8 characters, possibly incorrectly encoded';break;
				case JSON_ERROR_RECURSION:$msg ='One or more recursive references';break;
				case JSON_ERROR_INF_OR_NAN:$msg = 'One or more NAN or INF values';break;
				case JSON_ERROR_UNSUPPORTED_TYPE:$msg = 'A value of a type that cannot be encoded ';break;
				case JSON_ERROR_INVALID_PROPERTY_NAME:$msg =  'A property name that cannot be encoded';break;
				case JSON_ERROR_UTF16:'Malformed UTF-16 characters, possibly incorrectly encoded';break;
		        default: $msg = ' Unknown error';break;
		    }
			throw new Exception($msg, 1);
		}
		echo "\n $s";//exit();
		// if ($references[$fileName] !== $json) throw new Exception("\nError Processing $filename : \n$json", 1);
	}

}catch(Error $e)
{
	// echo $e->message;
	var_dump($e);
}