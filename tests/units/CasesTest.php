<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\{Yaml as Y, Loader};

final class Cases extends TestCase
{
    private $testFolder = __DIR__."/../cases/";
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

    // Batch tests
    // first declare Providers

    private function getGenerator($array) {
        $generator = function() use($array) {
            foreach ($array as $key => $value) {
                yield [$key, $value];
            }
        };
        return $generator();
    }

    public function examplesProvider()
    {
        $nameResultPair = get_object_vars(Y::parseFile(__DIR__.'/../definitions/examples_tests.yml'));
        $this->assertArrayHasKey('Example_2_01', $nameResultPair, 'ERROR during Yaml::parseFile for ../definitions/examples_tests.yml');
        return $this->getGenerator($nameResultPair);
    }

    public function parsingProvider()
    {
        $nameResultPair = get_object_vars(Y::parseFile(__DIR__.'/../definitions/parsing_tests.yml'));
        return $this->getGenerator($nameResultPair);
    }

    public function failingProvider()
    {
        $nameResultPair = get_object_vars(Y::parseFile(__DIR__.'/../definitions/failing_tests.yml'));
        return $this->getGenerator($nameResultPair);
    }

    /**
     * @dataProvider examplesProvider
     * @group cases
     */
    // public function testBatchExamples($fileName, $expected)
    // {
    //     $output = Y::parseFile($this->testFolder.'examples/'.$fileName.'.yml');
    //     $result = json_encode($output, self::JSON_OPTIONS);
    //     $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
    //     $this->assertEquals($expected, $result, is_array($output) ? $output[0]->getComment(1) : $output->getComment(1));
    // }

    /**
     * @dataProvider failingProvider
     * @group cases
     */
    // public function testBatchFailing($fileName)
    // {
    //     $content = file_get_contents($this->testFolder."/failing/$fileName.yml");
    //     // $this->assertInstanceOf(Y::parse($content), new \ParseError);
    //     $this->expectException(\Exception::class);
    //     // Y::parse($content);
    // }

    /**
     * @dataProvider parsingProvider
     * @group cases
     */
    public function testBatchParsing($fileName, $expected)
    {
        $yaml = file_get_contents($this->testFolder."parsing/$fileName.yml");
        $output = Y::parse($yaml, Loader::NO_PARSING_EXCEPTIONS);
        $result = json_encode($output, self::JSON_OPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        $this->assertEquals($expected, $result, $fileName.(is_null($output) ? "\t".Loader::$error : ''));
    }
}