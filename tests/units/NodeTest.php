<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeRoot;

/**
 * Class NodeTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Node
 */
class NodeTest extends TestCase
{
    /**
     * @var Node $node An instance of "Node" to test.
     */
    private $node;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->node = $this->getMockBuilder(Node::class)
                            ->setConstructorArgs(["a string to test", 1])
                            ->getMockForAbstractClass();
    }

    /**
     * @covers \Dallgoot\Yaml\Node::__construct
     */
    public function testConstruct(): void
    {
        $rawValue = '    somestring';
        $lineNumber = 45;
        $this->node->__construct($rawValue, $lineNumber);
        $this->assertEquals($rawValue, $this->node->raw);
        $this->assertEquals($lineNumber, $this->node->line);
        $this->assertEquals(4, $this->node->indent);
    }

    /**
     * @covers \Dallgoot\Yaml\Node::setParent
     */
    public function testSetParent(): void
    {
        $nodeRoot = new NodeRoot();
        $reflector = new \ReflectionClass($this->node);
        $method   = $reflector->getMethod('setParent');
        $property = $reflector->getProperty('_parent');
        $method->setAccessible(true);
        $property->setAccessible(true);

        $result  = $method->invoke($this->node, $nodeRoot);
        $this->assertTrue($result instanceof Node);
        $this->assertTrue($property->getValue($this->node) instanceof NodeRoot);
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getParent
     */
    public function testGetParent(): void
    {
        //direct parent : $indent = null
        $nodeRoot = new NodeRoot();
        $nodeRoot->add($this->node);
        $this->assertTrue($this->node->getParent() instanceof NodeRoot, 'parent is not a NodeRoot');
        //undirect parent : $indent = 2
        $nodeRoot = new NodeRoot();
        $nodeKey  = new NodeKey('  sequence:', 1);
        $nodeItem = new NodeItem('    -', 2);
        $nodeKeyInside = new NodeKey('       keyinitem: value', 3);
        $nodeKeyInside->add($this->node);
        $nodeItem->add($nodeKeyInside);
        $nodeKey->add($nodeItem);
        $nodeRoot->add($nodeKey);
        $this->assertEquals($nodeKey, $this->node->getParent(4));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getParent
     */
    public function testGetParentException(): void
    {
        $this->expectException(\Exception::class);
        $this->node->getParent();
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getRoot
     */
    public function testGetRoot(): void
    {
        $nodeRoot = new NodeRoot();
        $nodeKey  = new NodeKey('  sequence:', 1);
        $nodeItem = new NodeItem('    -', 2);
        $nodeKeyInside = new NodeKey('       keyinitem: value', 3);
        $nodeKeyInside->add($this->node);
        $nodeItem->add($nodeKeyInside);
        $nodeKey->add($nodeItem);
        $nodeRoot->add($nodeKey);
        $reflector = new \ReflectionClass($this->node);
        $getRoot = $reflector->getMethod('getRoot');
        $getRoot->setAccessible(true);
        $this->assertEquals($nodeRoot, $getRoot->invoke($this->node));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getRoot
     */
    public function testGetRootException(): void
    {
        $this->expectException(\Exception::class);
        $method = new \ReflectionMethod($this->node, 'getRoot');
        $method->setAccessible(true);
        $method->invoke($this->node, null);
    }
    /**
     * @covers \Dallgoot\Yaml\Node::add
     */
    public function testAdd(): void
    {
        // value is empty
        $this->assertEquals(null, $this->node->value);
        // add one Node
        $blankNode = new NodeBlank('', 1);
        $addResult = $this->node->add($blankNode);
        $this->assertEquals($blankNode, $addResult);
        $this->assertEquals($blankNode, $this->node->value);
        //value is already a node : add one
        $addResult2 = $this->node->add($blankNode);
        $this->assertEquals($blankNode, $addResult2);
        //  should change to NodeList
        $this->assertTrue($this->node->value instanceof NodeList);
        //and theres 2 children
        $this->assertEquals(2, $this->node->value->count());
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getDeepestNode
     */
    public function testGetDeepestNode(): void
    {
        $child = NodeFactory::get('    key: &anchor |', 1);
        $this->node->add($child);
        $this->assertTrue($child->getDeepestNode() instanceof NodeLit);
        $this->assertTrue($this->node->getDeepestNode() instanceof NodeLit);
    }

    /**
     * @covers \Dallgoot\Yaml\Node::specialProcess
     * @todo : test call for ALL NODETYPES using folder "types" listing and object creation
     */
    public function testSpecialProcess(): void
    {
        $previous = new NodeBlank('', 1);
        $blankBuffer = [];
        $this->assertFalse($this->node->specialProcess($previous, $blankBuffer));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $blankNode = new NodeBlank('', 1);
        $nodeRoot  = new NodeRoot();
        $nodeRoot->add($this->node);
        $this->assertEquals($nodeRoot, $this->node->getTargetOnEqualIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getTargetOnLessIndent
     *
     * @todo test with more content before this one
     */
    public function testGetTargetOnLessIndent(): void
    {
        $nodeRoot  = new NodeRoot();
        $keyNode = new NodeKey('sequence:', 1);
        $itemNode1 = new NodeItem('    - item1', 2);
        $itemNode2 = new NodeItem('    - item2', 3);
        $nodeRoot->add($keyNode);
        $keyNode->add($itemNode1);
        $this->assertEquals($keyNode, $itemNode1->getTargetOnLessIndent($itemNode2));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $previousNode = new NodeKey('emptykey:',1);
        $nextNode = new NodeItem('    - itemvalue',2);
        $this->assertEquals($previousNode, $previousNode->getTargetOnMoreIndent($nextNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $reflector = new \ReflectionClass($this->node);
        $isAwaitingChild = $reflector->getMethod('isAwaitingChild');
        $isAwaitingChild->setAccessible(true);
        $this->assertFalse($isAwaitingChild->invoke($this->node, new NodeBlank('', 1)));
    }

    /**
     * @covers \Dallgoot\Yaml\Node::__debugInfo
     */
    public function testDebugInfo(): void
    {
        $this->assertTrue(is_array($this->node->__debugInfo()));
    }
}
