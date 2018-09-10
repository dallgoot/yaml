<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\{Yaml as Y, Loader};

final class Yaml extends TestCase
{
    private $testFolder = __DIR__."/../cases/";
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

    // class specific tests

    public function testGetName($value='')
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testParse($value='')
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testParseFile($value='')
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testDump($value='')
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testDumpFile($value='')
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


}