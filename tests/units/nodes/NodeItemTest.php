<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\Node;

/**
 * Class NodeItemTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeItem
 */
class NodeItemTest extends TestCase
{
    /**
     * @var NodeItem $nodeItem An instance of "NodeItem" to test.
     */
    private $nodeItem;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeItem = new NodeItem("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::add
     */
    public function testAdd(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
