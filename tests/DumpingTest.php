<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml;

final class DumpingTest extends TestCase
{
    public function dumpingCasesProvider()
    {
        $nameResultPair = get_object_vars(Yaml::parseFile(__DIR__.'/definitions/dumping_tests.yml'));
        // var_dump($nameResultPair);//exit();
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
     */
    public function test_DumpingCases(string $fileName, string $expected)
    {
        $php = (include __DIR__."/cases/dumping/$fileName.php");
        $output = Yaml::dump($php);
        $this->assertEquals($expected, $output, "$fileName.php");
    }
}