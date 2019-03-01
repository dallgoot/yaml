<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeDocStart;
use Dallgoot\Yaml\Node;

/**
 * Class NodeDocStartTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeDocStart
 */
class NodeDocStartTest extends TestCase
{
    /**
     * @var NodeDocStart $nodeDocStart An instance of "NodeDocStart" to test.
     */
    private $nodeDocStart;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeDocStart = new NodeDocStart("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::add
     */
    public function testAdd(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
