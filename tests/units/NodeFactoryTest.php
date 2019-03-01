<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Node;

/**
 * Class NodeFactoryTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeFactory
 */
class NodeFactoryTest extends TestCase
{
    /**
     * @var NodeFactory $nodeFactory An instance of "NodeFactory" to test.
     */
    private $nodeFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeFactory = new NodeFactory();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::get
     */
    public function testGet(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onSpecial
     */
    public function testOnSpecial(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onQuoted
     */
    public function testOnQuoted(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onSetElement
     */
    public function testOnSetElement(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onCompact
     */
    public function testOnCompact(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onHyphen
     */
    public function testOnHyphen(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onNodeAction
     */
    public function testOnNodeAction(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onLiteral
     */
    public function testOnLiteral(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
