<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Yaml as Y;

final class DumperTest extends TestCase
{
    public function test__construct($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testToString($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testToFile($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testDump($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testDumpYamlObject($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testAdd($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testDumpSequence($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testInsertComments($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testDumpObject($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }

    public function testDumpCompact($value='')
     {
        $this->markTestIncomplete('This test has not been implemented yet.');
     }


    public function dumpingCasesProvider()
    {
        $nameResultPair = get_object_vars(Y::parseFile(__DIR__.'/../definitions/dumping_tests.yml'));
        $generator = function() use($nameResultPair) {
            foreach ($nameResultPair as $testName => $testResult) {
                yield [$testName, $testResult];
            }
        };
        return $generator();
    }

    /**
     * @dataProvider dumpingCasesProvider
     * @param string $fileName
     * @param string $expected
     * @throws Exception
     */
    public function test_DumpingCases(string $fileName, string $expected)
    {
        $php = (include __DIR__."/../cases/dumping/$fileName.php");
        $output = Y::dump($php);
        $this->assertEquals($expected, $output, "$fileName.php");
    }
}