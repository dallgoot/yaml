<?php

namespace Dallgoot\Yaml\tests\units;

require_once __DIR__ . '/../../vendor/autoload.php';

use atoum;

/**
 * @engine concurrent
 */
class Loader extends atoum
{
	private const EXAMPLES_FOLDER = __DIR__."/../../references/";

	public function test__constructWithNothing()
	{
		// $this->newTestedInstance();
		$loaderMock = $this->newTestedInstance();
		$myClassReflection = new \ReflectionClass(get_class($loaderMock));
	    $debug = $myClassReflection->getProperty('debug');
        $debug->setAccessible(true);
	    $options = $myClassReflection->getProperty('options');
        $options->setAccessible(true);
        $defaultOptions = $this->testedInstance::EXCLUDE_DIRECTIVES &
                      	  $this->testedInstance::IGNORE_COMMENTS &
                      	  $this->testedInstance::EXCEPTIONS_PARSING &
                      	  $this->testedInstance::NO_OBJECT_FOR_DATE;
        $this
		    ->given($loaderMock)
		    ->then
		        ->integer($debug->getValue($loaderMock))->isIdenticalTo(0)
		        ->integer($options->getValue($loaderMock))->isIdenticalTo($defaultOptions);
		;
	}


	// public function loadDataProvider()
	// {
	// 	return new RecursiveDirectoryIterator(EXAMPLES_FOLDER, FilesystemIterator::SKIP_DOTS);
	// }

	// public function testLoad(string $fileName)
	// {
	// 	$this->
	// }



	// public function onSpecialType($value='')
	// {
	// 	# code...
	// }

	// public function onDeepestType($value='')
	// {
	// 	# code...
	// }
}