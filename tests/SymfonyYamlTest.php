<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class SymfonyYamlCases extends TestCase
{
    private $testFolder = __DIR__."/cases/";
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR;

    // Batch tests
    // first declare Providers

    private function getGenerator($array) {
        $generator = function() use($array) {
            foreach ($array as $key => $value) {
                yield [$key, rtrim($value)];
            }
        };
        return $generator();
    }

    public function examplestestSymfonyProvider()
    {
        $nameResultPair = Yaml::parseFile(__DIR__.'/definitions/examples_tests.yml');
        $this->assertArrayHasKey('Example_2_01', $nameResultPair, 'ERROR during Yaml::parseFile for ../definitions/examples_tests.yml');
        return $this->getGenerator($nameResultPair);
    }

    public function parsingtestSymfonyProvider()
    {
        $nameResultPair = Yaml::parseFile(__DIR__.'/definitions/parsing_tests.yml');
        return $this->getGenerator($nameResultPair);
    }

    public function failingtestSymfonyProvider()
    {
        $nameResultPair = Yaml::parseFile(__DIR__.'/definitions/failing_tests.yml');
        return $this->getGenerator($nameResultPair);
    }

    /**
     * @dataProvider examplesProvider
     * @group cases
     */
    public function testSymfonyBatchExamples($fileName, $expected)
    {
        $output = Yaml::parseFile($this->testFolder.'examples/'.$fileName.'.yml');
        $result = json_encode($output, self::JSON_OPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider failingtestSymfonyProvider
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
     * @dataProvider parsingtestSymfonyProvider
     * @group cases
     * @todo verify that every test file has a result in tests/definitions/parsing.yml
     */
    public function testSymfonyBatchParsing($fileName, $expected)
    {
        $yaml = file_get_contents($this->testFolder."parsing/$fileName.yml");
        $output = Yaml::parse($yaml, Yaml::PARSE_CUSTOM_TAGS);
        $result = json_encode($output, self::JSON_OPTIONS);
        $this->assertContains(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_INF_OR_NAN], json_last_error_msg());
        $this->assertEquals($expected, $result);
    }
}