<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\DumperHandlers;
use Dallgoot\Yaml\YamlObject;

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
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dump
     */
    public function testDump()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpScalar
     */
    public function testDumpScalar()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpCompound
     */
    public function testDumpCompound()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpYamlObject
     */
    public function testDumpYamlObject()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::IteratorToString
     */
    public function testIteratorToString()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpCompact
     */
    public function testDumpCompact()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpString
     */
    public function testDumpString()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\DumperHandlers::dumpTagged
     */
    public function testDumpTagged()
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

}