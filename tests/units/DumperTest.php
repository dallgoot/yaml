<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Dumper;
use Dallgoot\Yaml\YamlObject;

/**
 * Class DumperTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Dumper
 */
class DumperTest extends TestCase
{
    /**
     * @var Dumper $dumper An instance of "Dumper" to test.
     */
    private $dumper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->dumper = new Dumper();
    }
    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::__construct
     */
    public function test__construct()
    {
        $this->dumper->__construct(1);
        $reflector = new \ReflectionClass($this->dumper);
        $optionsProp = $reflector->getProperty('options');
        $optionsProp->setAccessible(true);
        $this->assertEquals(1, $optionsProp->getValue($this->dumper));
    }
    /**
     * @covers \Dallgoot\Yaml\Dumper::toString
     */
    public function testToString(): void
    {
        $this->assertEquals("- 1\n- 2\n- 3", $this->dumper->toString([1,2,3]));
        $this->assertEquals("--- some text\n", $this->dumper->toString('some text'));
    }

    /**
     * @covers \Dallgoot\Yaml\Dumper::toFile
     */
    public function testToFile(): void
    {
        $filename = 'dumperTest.yml';
        $result = $this->dumper->toFile($filename, [1,2,3]);
        $this->assertTrue($result);
        $this->assertEquals("- 1\n- 2\n- 3", file_get_contents($filename));
        unlink($filename);
    }
    /**
     * @covers \Dallgoot\Yaml\Dumper::dump
     */
    public function testDump()
    {
        $this->assertEquals('', $this->dumper->dump(null, 0));
        $this->assertEquals('stream', $this->dumper->dump(fopen(__FILE__, 'r'), 0));
        $this->assertEquals('str', $this->dumper->dump('str', 0));
        $this->assertEquals('- 1', $this->dumper->dump([1], 0));
        $o = new \Stdclass;
        $o->prop = 1;
        $this->assertEquals('prop: 1', $this->dumper->dump($o, 0));
    }


    /**
     * @covers \Dallgoot\Yaml\Dumper::dumpYamlObject
     */
    public function testDumpYamlObject()
    {
        $dumpYamlObject = new \ReflectionMethod($this->dumper, 'dumpYamlObject');
        $dumpYamlObject->setAccessible(true);
        $yamlObject = new YamlObject(0);
        $yamlObject->a = 1;
        $this->assertEquals('a: 1', $dumpYamlObject->invoke($this->dumper, $yamlObject, 0));
        unset($yamlObject->a);
        $yamlObject[0] = 'a';
        $this->assertEquals('- a', $dumpYamlObject->invoke($this->dumper, $yamlObject, 0));
    }

    /**
     * @covers \Dallgoot\Yaml\Dumper::IteratorToString
     */
    public function testIteratorToString()
    {
        $iteratorToString = new \ReflectionMethod($this->dumper, 'iteratorToString');
        $iteratorToString->setAccessible(true);
        $yamlObject = new YamlObject(0);
        $yamlObject[0] = 'a';
        $yamlObject[1] = 'b';
        $this->assertEquals("- a\n- b", $iteratorToString->invoke($this->dumper, $yamlObject, '-', "\n", 0));
    }

}
