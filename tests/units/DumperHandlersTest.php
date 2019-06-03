<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\DumperHandlers;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Compact;
use Dallgoot\Yaml\Tagged;

/**
 * Class DumperHandlersTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\DumperHandlers
 */
class DumperHandlersTest extends TestCase
{
    /**
     * @var DumperHandlers $dumperHandler An instance of "DumperHandlers" to test.
     */
    public $dumperHandler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->dumperHandler = new DumperHandlers();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::__construct
     */
    public function test__construct()
    {
        $this->dumperHandler->__construct(1);
        $reflector = new \ReflectionClass($this->dumperHandler);
        $optionsProp = $reflector->getProperty('options');
        $optionsProp->setAccessible(true);
        $this->assertEquals(1, $optionsProp->getValue($this->dumperHandler));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dump
     */
    public function testDump()
    {
        $this->assertEquals('', $this->dumperHandler->dump(null, 0));
        $this->assertEquals('stream', $this->dumperHandler->dump(fopen(__FILE__, 'r'), 0));
        $this->assertEquals('str', $this->dumperHandler->dump('str', 0));
        $this->assertEquals('- 1', $this->dumperHandler->dump([1], 0));
        $o = new \Stdclass;
        $o->prop = 1;
        $this->assertEquals('prop: 1', $this->dumperHandler->dump($o, 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpScalar
     */
    public function testDumpScalar()
    {
        $this->assertEquals('.inf', $this->dumperHandler->dumpScalar(\INF));
        $this->assertEquals('-.inf', $this->dumperHandler->dumpScalar(-\INF));
        $this->assertEquals('true', $this->dumperHandler->dumpScalar(true));
        $this->assertEquals('false', $this->dumperHandler->dumpScalar(false));
        $this->assertEquals('.nan', $this->dumperHandler->dumpScalar(\NAN));
        $this->assertEquals('0.4500', $this->dumperHandler->dumpScalar(0.45));
        $this->assertEquals('0.1235', $this->dumperHandler->dumpScalar(0.123456));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpCompound
     */
    public function testDumpCompoundException()
    {
        $callable = function () {return false;};
        $this->expectException(\Exception::class);
        $dumpCompound = new \ReflectionMethod($this->dumperHandler, 'dumpCompound');
        $dumpCompound->setAccessible(true);
        $dumpCompound->invoke($this->dumperHandler, $callable, 0);
    }
    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpCompound
     */
    public function testDumpCompound()
    {
        $dumpCompound = new \ReflectionMethod($this->dumperHandler, 'dumpCompound');
        $dumpCompound->setAccessible(true);
        $yamlObject = new YamlObject;
        $yamlObject->a = 1;
        $this->assertEquals('a: 1', $dumpCompound->invoke($this->dumperHandler, $yamlObject, 0));
        unset($yamlObject->a);
        $yamlObject[0] = 'a';
        $this->assertEquals('- a', $dumpCompound->invoke($this->dumperHandler, $yamlObject, 0));
        $compact = new Compact([1,2,3]);
        $this->assertEquals('[1, 2, 3]', $dumpCompound->invoke($this->dumperHandler, $compact, 0));
        $o = new \Stdclass;
        $o->a = 1;
        $compact = new Compact($o);
        $this->assertEquals('{a: 1}', $dumpCompound->invoke($this->dumperHandler, $compact, 0));
        $this->assertEquals("- 1\n- 2\n- 3", $dumpCompound->invoke($this->dumperHandler, [1,2,3], 0));
        $tagged = new Tagged('!!str', 'somestring');
        $this->assertEquals("!!str somestring", $dumpCompound->invoke($this->dumperHandler, $tagged, 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpYamlObject
     */
    public function testDumpYamlObject()
    {
        $dumpYamlObject = new \ReflectionMethod($this->dumperHandler, 'dumpYamlObject');
        $dumpYamlObject->setAccessible(true);
        $yamlObject = new YamlObject;
        $yamlObject->a = 1;
        $this->assertEquals('a: 1', $dumpYamlObject->invoke($this->dumperHandler, $yamlObject, 0));
        unset($yamlObject->a);
        $yamlObject[0] = 'a';
        $this->assertEquals('- a', $dumpYamlObject->invoke($this->dumperHandler, $yamlObject, 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::IteratorToString
     */
    public function testIteratorToString()
    {
        $iteratorToString = new \ReflectionMethod($this->dumperHandler, 'iteratorToString');
        $iteratorToString->setAccessible(true);
        $yamlObject = new YamlObject;
        $yamlObject[0] = 'a';
        $yamlObject[1] = 'b';
        $this->assertEquals("- a\n- b", $iteratorToString->invoke($this->dumperHandler, $yamlObject, '-', 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpCompact
     */
    public function testDumpCompact()
    {
       $this->assertEquals("[1, 2, 3]", $this->dumperHandler->dumpCompact([1,2,3], 0));
       $o = new \Stdclass;
       $o->a = 1;
       $o->b = [1, 2];
       $o->c = new \Stdclass;
       $o->c->ca = 1;
       $this->assertEquals("{a: 1, b: [1, 2], c: {ca: 1}}", $this->dumperHandler->dumpCompact($o, 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpString
     */
    public function testDumpString()
    {
       $this->assertEquals('abc   ', $this->dumperHandler->dumpString('   abc   ', 0));
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpTagged
     */
    public function testDumpTagged()
    {
       $tagged = new Tagged('!!str', 'somestring');
       $this->assertEquals("!!str somestring", $this->dumperHandler->dumpTagged($tagged, 0));
       $tagged = new Tagged('!!omap', [1,2]);
       $this->assertEquals("!!omap\n  - 1\n  - 2", $this->dumperHandler->dumpTagged($tagged, 0));
    }

}