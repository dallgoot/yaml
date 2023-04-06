<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Nodes\SetKey;
use Dallgoot\Yaml\Nodes\SetValue;

/**
 * Class ItemTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Item
 */
class ItemTest extends TestCase
{
    /**
     * @var Item $nodeItem An instance of "Nodes\Item" to test.
     */
    private $nodeItem;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeItem = new Item("  -", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue(is_null($this->nodeItem->value));
        $this->nodeItem = new Item("  - itemvalue", 42);
        $this->assertTrue($this->nodeItem->value instanceof Scalar);
        $this->nodeItem = new Item("  - keyinside: keyvalue", 42);
        $this->assertTrue($this->nodeItem->value instanceof Key);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::add
     */
    public function testAdd(): void
    {
        $this->assertTrue(is_null($this->nodeItem->value));
        //
        $this->nodeItem = new Item('  - keyinside: keyvalue', 1);
        $keyNode        = new Key('    anotherkey: anothervalue', 3);
        $this->nodeItem->add($keyNode);
        $this->assertTrue($this->nodeItem->value instanceof NodeList);
        //
        $this->nodeItem = new Item('  - keyinside:', 3);
        $keyNode        = new Key('      childkey: anothervalue', 4);
        $this->nodeItem->add($keyNode);
        $keyinside = $this->nodeItem->value;
        $this->assertEquals($keyNode, $keyinside->value);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::add
     */
    public function testAddException(): void
    {
        $this->expectException(\ParseError::class);
        $this->nodeItem = new Item('  - keyinside: keyvalue', 1);
        $keyNode        = new Key('        anotherkey: anothervalue', 3);
        $this->nodeItem->add($keyNode);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $rootNode = new Root;
        $rootNode->add($this->nodeItem);
        $itemNode = new Item('- item2', 1);
        $this->assertEquals($rootNode, $this->nodeItem->getTargetOnEqualIndent($itemNode));
        //
        $rootNode = new Root;
        $this->nodeItem = new Item('- sameindentitem', 3);
        $keyNode = new Key('key_with_no_indent_a_sequence:', 1);
        $rootNode->add($keyNode);
        $keyNode->add($this->nodeItem);
        // $this->nodeItem->add($keyNode);
        $itemNode2 = new Item('- item_with_no_indent: 123', 2);
        $parent = $this->nodeItem->getTargetOnEqualIndent($itemNode2);
        $this->assertEquals($rootNode, $parent);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $blankNode = new Blank('', 1);
        $this->assertEquals($this->nodeItem, $this->nodeItem->getTargetOnMoreIndent($blankNode));
        $keyNode = new Key('key:', 1);
        $this->nodeItem->add($keyNode);
        $this->assertEquals($keyNode, $this->nodeItem->getTargetOnMoreIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::build
     */
    public function testBuild(): void
    {
        // no parent
        $this->assertEquals([null], $this->nodeItem->build());
        $parent = [];
        $this->assertEquals(null, $this->nodeItem->build($parent));
        $this->assertEquals([0 => null], $parent);
        $parent = new YamlObject(0);
        $this->assertEquals(null, $this->nodeItem->build($parent));
        $this->assertArrayHasKey(0, $parent);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::build
     */
    public function testBuildWhenParentIsString()
    {
        $this->expectException(\Exception::class);
        $parent = '';
        $this->nodeItem->build($parent);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::build
     */
    public function testBuildWhenParentIsObject()
    {
        $this->expectException(\Exception::class);
        $parent = new \stdClass;
        $this->nodeItem->build($parent);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Item::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $blankNode = new Blank('', 1);
        $this->assertTrue($this->nodeItem->isAwaitingChild($blankNode));
        //
        $setkeyNode = new SetKey('? setkey', 1);
        $setvalueNode = new SetValue(': setvalue', 2);
        $this->nodeItem->add($setkeyNode);
        $this->assertTrue($this->nodeItem->isAwaitingChild($setvalueNode));
        //
        $this->nodeItem->value = null;
        $keyNode = new Key(' key: ', 42);
        $scalarNode = new Scalar('  some text', 43);
        $this->nodeItem->add($keyNode);
        $this->assertTrue($this->nodeItem->isAwaitingChild($scalarNode));
    }
}
