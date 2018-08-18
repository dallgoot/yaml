<?php

namespace Dallgoot\Yaml\tests\units;

use Dallgoot\Yaml as Y;
use atoum;

/**
 *  serves as a base for generating testing cases classes
 */
class BaseTest extends atoum
{
	private $source;

	public function init($fileName)
	{
		$this->source = Y::parseFile($fileName);
	}

	private function test_expectParsing($fileName, $jsonString)
    {
        $this->given($result = $this->newTestedInstance::parseFile($fileName))
             ->string(json_encode($result, self::JSONOPTIONS))
             ->isIdenticalTo($jsonString);
    }

	private function test_expectParseError($yaml, $expectedMessage)
    {
        $this->exception($this->newTestedInstance::parse($yaml))
             ->isIdenticalTo(new \ParseError($expectedMessage));
    }

    private function test_expectDumping($phpVar, $yamlString)
    {
        $this->given($phpVar)
             ->string(Y::dump('%s'))
             ->isIdenticalTo($yamlString);
    }
}
