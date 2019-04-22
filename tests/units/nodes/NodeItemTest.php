<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeSetKey;
use Dallgoot\Yaml\NodeSetValue;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeItemTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
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
        $this->nodeItem = new NodeItem("  -", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue(is_null($this->nodeItem->value));
        $this->nodeItem = new NodeItem("  - itemvalue", 42);
        $this->assertTrue($this->nodeItem->value instanceof NodeScalar);
        $this->nodeItem = new NodeItem("  - keyinside: keyvalue", 42);
        $this->assertTrue($this->nodeItem->value instanceof NodeKey);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::add
     */
    public function testAdd(): void
    {
        $this->assertTrue(is_null($this->nodeItem->value));
        //
        $this->nodeItem = new NodeItem('  - keyinside: keyvalue', 1);
        $keyNode        = new  NodeKey('    anotherkey: anothervalue', 3);
        $this->nodeItem->add($keyNode);
        $this->assertTrue($this->nodeItem->value instanceof NodeList);
        //
        $this->nodeItem = new NodeItem('  - keyinside:', 3);
        $keyNode        = new  NodeKey('      childkey: anothervalue', 4);
        $this->nodeItem->add($keyNode);
        $keyinside = $this->nodeItem->value;
        $this->assertEquals($keyNode, $keyinside->value);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::add
     */
    public function testAddException(): void
    {
        $this->expectException(\ParseError::class);
        $this->nodeItem = new NodeItem('  - keyinside: keyvalue', 1);
        $keyNode        = new  NodeKey('        anotherkey: anothervalue', 3);
        $this->nodeItem->add($keyNode);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $rootNode = new NodeRoot;
        $rootNode->add($this->nodeItem);
        $itemNode = new NodeItem('- item2', 1);
        $this->assertEquals($rootNode, $this->nodeItem->getTargetOnEqualIndent($itemNode));
        //
        $rootNode = new NodeRoot;
        $this->nodeItem = new NodeItem('- sameindentitem', 3);
        $keyNode = new NodeKey('key_with_no_indent_a_sequence:', 1);
        $rootNode->add($keyNode);
        $keyNode->add($this->nodeItem);
        // $this->nodeItem->add($keyNode);
        $itemNode2 = new NodeItem('- item_with_no_indent: 123', 2);
        $parent = $this->nodeItem->getTargetOnEqualIndent($itemNode2);
        $this->assertEquals($rootNode, $parent);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $blankNode = new NodeBlank('', 1);
        $this->assertEquals($this->nodeItem, $this->nodeItem->getTargetOnMoreIndent($blankNode));
        $keyNode = new NodeKey('key:', 1);
        $this->nodeItem->add($keyNode);
        $this->assertEquals($keyNode, $this->nodeItem->getTargetOnMoreIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::build
     */
    public function testBuild(): void
    {
        // no parent
        $this->assertEquals([null], $this->nodeItem->build());
        $parent = [];
        $this->assertEquals(null, $this->nodeItem->build($parent));
        $this->assertEquals([0 => null], $parent);
        $parent = new YamlObject;
        $this->assertEquals(null, $this->nodeItem->build($parent));
        $this->assertArrayHasKey(0, $parent);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::build
     */
    public function testBuildWhenParentIsString()
    {
        $this->expectException(\Exception::class);
        $parent = '';
        $this->nodeItem->build($parent);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::build
     */
    public function testBuildWhenParentIsObject()
    {
        $this->expectException(\Exception::class);
        $parent = new \StdClass;
        $this->nodeItem->build($parent);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeItem::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $blankNode = new NodeBlank('', 1);
        $this->assertTrue($this->nodeItem->isAwaitingChild($blankNode));
        //
        $setkeyNode = new NodeSetKey('? setkey', 1);
        $setvalueNode = new NodeSetValue(': setvalue', 2);
        $this->nodeItem->add($setkeyNode);
        $this->assertTrue($this->nodeItem->isAwaitingChild($setvalueNode));
        //
        $this->nodeItem->value = null;
        $keyNode = new NodeKey(' key: ', 42);
        $scalarNode = new NodeScalar('  some text', 43);
        $this->nodeItem->add($keyNode);
        $this->assertTrue($this->nodeItem->isAwaitingChild($scalarNode));
    }
}
