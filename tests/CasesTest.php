<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml as Yaml;

final class Cases extends TestCase
{
    private $testFolder = __DIR__."/cases/";
    // private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;
    // private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES  | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;
    const JSON_OPTIONS = JSON_PRESERVE_ZERO_FRACTION |JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES  |JSON_PARTIAL_OUTPUT_ON_ERROR;

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
        $nameResultPair = get_object_vars(Yaml::parseFile(__DIR__.'/definitions/examples_tests.yml'));
        $this->assertArrayHasKey('Example_2_01', $nameResultPair, 'ERROR during Yaml::parseFile for ../definitions/examples_tests.yml');
        $this->assertEquals(28, count($nameResultPair));
        return $this->getGenerator($nameResultPair);
    }

    public function parsingProvider()
    {
        $nameResultPair = get_object_vars(Yaml::parseFile(__DIR__.'/definitions/parsing_tests.yml'));//var_dump($nameResultPair);die();
        $this->assertEquals(58, count($nameResultPair));
        return $this->getGenerator($nameResultPair);
    }

    // public function failingProvider()
    // {
    //     $nameResultPair = get_object_vars(Yaml::parseFile(__DIR__.'/definitions/failing_tests.yml'));
    //     $this->assertEquals(20, count($nameResultPair));
    //     return $this->getGenerator($nameResultPair);
    // }

    /**
     * @dataProvider examplesProvider
     */
    public function testBatchExamples($fileName, $expected)
    {
        Yaml\TagFactory::$schemas = [];
        Yaml\TagFactory::$schemaHandles = [];
        $output = Yaml::parseFile(__DIR__."/cases/examples/$fileName.yml");
        $result = json_encode($output, self::JSON_OPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        // $this->assertEquals($expected, $result, is_array($output) ? $output[0]->getComment(1) : $output->getComment(1));
        $this->assertEquals($expected, $result, $fileName.(is_null($output) ? "\t".Yaml\Loader::$error : ''));
    }

    /**
     * @dataProvider failingProvider
     */
    // public function testBatchFailing($fileName)
    // {
    //     $content = file_get_contents($this->testFolder."/failing/$fileName.yml");
    //     // $this->assertInstanceOf(Yaml::parse($content), new \ParseError);
    //     $this->expectException(\Exception::class);
    //     // Yaml::parse($content);
    // }

    /**
     * @dataProvider parsingProvider
     * @todo verify that every test file has a result in tests/definitions/parsing.yml
     */
    public function testBatchParsing($fileName, $expected)
    {
        Yaml\TagFactory::$schemas = [];
        Yaml\TagFactory::$schemaHandles = [];
        $yaml = file_get_contents(__DIR__."/cases/parsing/$fileName.yml");
        $output = Yaml::parse($yaml, Yaml\Loader::NO_PARSING_EXCEPTIONS);
        $result = json_encode($output, self::JSON_OPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        $this->assertEquals($expected, $result, $fileName.(is_null($output) ? "\t".Yaml\Loader::$error : ''));
    }
}