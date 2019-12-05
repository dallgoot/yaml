<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\LiteralFolded;
use Dallgoot\Yaml\Nodes\Root;

/**
 * Class NodeGenericTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\NodeGeneric
 */
class NodeGenericTest extends TestCase
{
    /**
     * @var NodeGeneric $node An instance of "NodeGeneric" to test.
     */
    private $node;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->node = $this->getMockBuilder(NodeGeneric::class)
                            ->setConstructorArgs(["a string to test", 1])
                            ->getMockForAbstractClass();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::__construct
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
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::setParent
     */
    public function testSetParent(): void
    {
        $nodeRoot = new Root();
        $reflector = new \ReflectionClass($this->node);
        $method   = $reflector->getMethod('setParent');
        $property = $reflector->getProperty('_parent');
        $method->setAccessible(true);
        $property->setAccessible(true);

        $result  = $method->invoke($this->node, $nodeRoot);
        $this->assertTrue($result instanceof NodeGeneric);
        $this->assertTrue($property->getValue($this->node) instanceof Root);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getParent
     */
    public function testGetParent(): void
    {
        //direct parent : $indent = null
        $nodeRoot = new Root();
        $nodeRoot->add($this->node);
        $this->assertTrue($this->node->getParent() instanceof Root, 'parent is not a NodeRoot');
        //undirect parent : $indent = 2
        $nodeRoot = new Root();
        $nodeKey  = new Key('  sequence:', 1);
        $nodeItem = new Item('    -', 2);
        $nodeKeyInside = new Key('       keyinitem: value', 3);
        $nodeKeyInside->add($this->node);
        $nodeItem->add($nodeKeyInside);
        $nodeKey->add($nodeItem);
        $nodeRoot->add($nodeKey);
        $this->assertEquals($nodeKey, $this->node->getParent(4));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getParent
     */
    public function testGetParentException(): void
    {
        $this->expectException(\Exception::class);
        $this->node->getParent();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getRoot
     */
    public function testGetRoot(): void
    {
        $nodeRoot = new Root();
        $nodeKey  = new Key('  sequence:', 1);
        $nodeItem = new Item('    -', 2);
        $nodeKeyInside = new Key('       keyinitem: value', 3);
        $nodeKeyInside->add($this->node);
        $nodeItem->add($nodeKeyInside);
        $nodeKey->add($nodeItem);
        $nodeRoot->add($nodeKey);
        $getRoot = new \ReflectionMethod($this->node, 'getRoot');
        $getRoot->setAccessible(true);
        $this->assertEquals($nodeRoot, $getRoot->invoke($this->node));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getRoot
     */
    public function testGetRootException(): void
    {
        $this->expectException(\Exception::class);
        $method = new \ReflectionMethod($this->node, 'getRoot');
        $method->setAccessible(true);
        $method->invoke($this->node, null);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::add
     */
    public function testAdd(): void
    {
        // value is empty
        $this->assertEquals(null, $this->node->value);
        // add one Node
        $blankNode = new Blank('', 1);
        $addResult = $this->node->add($blankNode);
        $this->assertEquals($blankNode, $addResult);
        $this->assertEquals($blankNode, $this->node->value);
        //value is already a NodeGeneric : add one
        $addResult2 = $this->node->add($blankNode);
        $this->assertEquals($blankNode, $addResult2);
        //  should change to NodeList
        $this->assertTrue($this->node->value instanceof NodeList);
        //and theres 2 children
        $this->assertEquals(2, $this->node->value->count());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getDeepestNode
     */
    public function testGetDeepestNode(): void
    {
        $child = NodeFactory::get('    key: &anchor |', 1);
        $this->node->add($child);//var_dump($child->getDeepestNode());
        $this->assertTrue($child->getDeepestNode() instanceof Literal);
        $this->assertTrue($this->node->getDeepestNode() instanceof Literal);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::specialProcess
     * @todo : test call for ALL NODETYPES using folder "types" listing and object creation
     */
    public function testSpecialProcess(): void
    {
        $previous = new Blank('', 1);
        $blankBuffer = [];
        $this->assertFalse($this->node->specialProcess($previous, $blankBuffer));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $blankNode = new Blank('', 1);
        $nodeRoot  = new Root();
        $nodeRoot->add($this->node);
        $this->assertEquals($nodeRoot, $this->node->getTargetOnEqualIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getTargetOnLessIndent
     *
     * @todo test with more content before this one
     */
    public function testGetTargetOnLessIndent(): void
    {
        $nodeRoot  = new Root();
        $keyNode = new Key('sequence:', 1);
        $itemNode1 = new Item('    - item1', 2);
        $itemNode2 = new Item('    - item2', 3);
        $nodeRoot->add($keyNode);
        $keyNode->add($itemNode1);
        $this->assertEquals($keyNode, $itemNode1->getTargetOnLessIndent($itemNode2));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $previousNode = new Key('emptykey:',1);
        $nextNode = new Item('    - itemvalue',2);
        $this->assertEquals($previousNode, $previousNode->getTargetOnMoreIndent($nextNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $isAwaitingChild = new \ReflectionMethod($this->node, 'isAwaitingChild');
        $isAwaitingChild->setAccessible(true);
        $this->assertFalse($isAwaitingChild->invoke($this->node, new Blank('', 1)));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::isOneOf
     */
    public function testIsOneOf(): void
    {
        $rootNode = new Root;
        $this->assertTrue($rootNode->isOneOf('Root'));
        $this->assertFalse($rootNode->isOneOf('Key'));
        $this->assertFalse($rootNode->isOneOf('Blank'));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\NodeGeneric::__debugInfo
     */
    public function testDebugInfo(): void
    {
        $this->assertTrue(is_array($this->node->__debugInfo()));
    }
}
