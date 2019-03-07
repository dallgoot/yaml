<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeKeyTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeKey
 */
class NodeKeyTest extends TestCase
{
    /**
     * @var NodeKey $nodeKey An instance of "NodeKey" to test.
     */
    private $nodeKey;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeKey = new NodeKey("key: value", 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeKey->value instanceof NodeScalar);
        $this->nodeKey = new NodeKey("key:", 1);
        $this->assertTrue(is_null($this->nodeKey->value));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::setIdentifier
     */
    public function testSetIdentifier(): void
    {
        $reflector = new \ReflectionClass($this->nodeKey);
        $identifier = $reflector->getProperty('identifier');
        $identifier->setAccessible(true);

        $this->nodeKey->setIdentifier('newkey');
        $this->assertEquals('newkey', $identifier->getValue($this->nodeKey));

        $this->nodeKey->setIdentifier('!!str 1.2');
        $this->assertEquals('!!str', $this->nodeKey->tag);
        $this->assertEquals('1.2', $identifier->getValue($this->nodeKey));

        $this->nodeKey->setIdentifier('&anchor 1.2');
        $this->assertEquals('&anchor', $this->nodeKey->anchor);
        $this->assertEquals('1.2', $identifier->getValue($this->nodeKey));
    }

    private function setIdentifierAsEmptyString()
    {
        $this->expectException(\Exception::class);
        $this->nodeKey->setIdentifier('');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::add
     */
    public function testAdd(): void
    {
        $scalarNode = new NodeScalar('sometext', 2);
        $this->nodeKey = new NodeKey("key:", 1);
        $this->nodeKey->add($scalarNode);
        $this->assertEquals($scalarNode, $this->nodeKey->value);
        //
        $this->nodeKey = new NodeKey("key: |", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLit = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLit->value->offsetGet(0));
        //
        $this->nodeKey = new NodeKey("key: >", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLitFolded = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLitFolded->value->offsetGet(0));
        //
        $this->nodeKey = new NodeKey("key: &anchor", 1);
        $this->nodeKey->add($scalarNode);
        $nodeAnchor = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeAnchor->value);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
