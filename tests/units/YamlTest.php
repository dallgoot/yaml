<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Yaml as Y;

final class Yaml extends TestCase
{
    private $folder = __DIR__."/../cases/";
    private const JSONOPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

    // public function setUp()
    // {
    //     // $this->examples = Y::parseFile('./definitions/examples_tests.yml');
    // }

    /**
     * @dataProvider test_examplesProvider
     */
    public function test_examples($fileName, $expected)
    {
        $output = Y::parseFile($this->folder.'examples/'.$fileName.'.yml');
        // echo "\n".(is_array($output) ? $output[0]->getComment(1) : $output->getComment(1));
        $result = json_encode($output, self::JSONOPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        $this->assertEquals($expected, $result, is_array($output) ? $output[0]->getComment(1) : $output->getComment(1));
    }

    public function test_examplesProvider()
    {
        $nameResultPair = get_object_vars(Y::parseFile(__DIR__.'/../definitions/examples_tests.yml'));
        $this->assertArrayHasKey('Example_2_01', $nameResultPair, 'ERROR during Yaml::parseFile for ../definitions/examples_tests.yml');
        $generator = function() use($nameResultPair) {
            foreach ($nameResultPair as $key => $value) {
                yield [$key, $value];
            }
        };
        return $generator();
    }



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