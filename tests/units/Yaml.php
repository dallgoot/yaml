<?php
declare(strict_types=1);

// namespace Dallgoot\tests\units;
use PHPUnit\Framework\TestCase;
// require_once __DIR__ . '/../../vendor/autoload.php';
// include __DIR__ .'/../tests_ref.php';
// include __DIR__ .''

// use atoum;
use Dallgoot\Yaml\Yaml as Y;

/**
 * @engine concurrent
 *
 */
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
        $this->assertEquals($result, $expected, is_array($output) ? $output[0]->getComment(1) : $output->getComment(1));
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

    // private function stringResultTest($yaml, $expected)
    // {
    //     $this->given($result = $this->newTestedInstance::parse($yaml))
    //          ->isIdenticalTo($expected)
    //          ->string(json_encode($result, self::JSONOPTIONS));
    // }


}