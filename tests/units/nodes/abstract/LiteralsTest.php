<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Comment;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\LiteralFolded;
use Dallgoot\Yaml\Nodes\Literals;
use Dallgoot\Yaml\Nodes\Quoted;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class LiteralsTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Literals
 */
class LiteralsTest extends TestCase
{
    /**
     * @var Literals $nodeLiterals An instance of "Nodes\Literals" to test.
     */
    private $nodeLiterals;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs(["|-", 42])
            ->getMockForAbstractClass();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::__construct
     */
    public function testConstruct(): void
    {
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs(["|", 42])
            ->getMockForAbstractClass();
        $reflector = new \ReflectionClass($this->nodeLiterals);
        $identifier = $reflector->getProperty('identifier');
        $identifier->setAccessible(true);
        $this->assertEquals(null, $identifier->getValue($this->nodeLiterals));
        //
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs(["|-", 42])
            ->getMockForAbstractClass();
        //
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs(["|+", 42])
            ->getMockForAbstractClass();
        $this->assertEquals('+', $identifier->getValue($this->nodeLiterals));
        //
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs([">-", 42])
            ->getMockForAbstractClass();
        //
        $this->nodeLiterals = $this->getMockBuilder(Literals::class)
            ->setConstructorArgs([">+", 42])
            ->getMockForAbstractClass();
        $this->assertEquals('+', $identifier->getValue($this->nodeLiterals));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::add
     */
    public function testAdd(): void
    {
        $this->assertTrue(is_null($this->nodeLiterals->value));
        $this->nodeLiterals->add(new Scalar(' some text', 2));
        $this->assertTrue($this->nodeLiterals->value instanceof NodeList);
        //
        $this->nodeLiterals->value = null;
        $falseItem = new Item(' - not an item', 2);

        $this->assertTrue($this->nodeLiterals->add($falseItem) instanceof Scalar);
        $this->assertFalse($this->nodeLiterals->value->offsetGet(0) instanceof Item);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::litteralStripLeading
     */
    public function testLitteralStripLeading(): void
    {
        $list = new NodeList(new Blank('', 1));
        $list->push(new Blank('', 2));
        $stripLeading = new \ReflectionMethod(Literals::class, 'litteralStripLeading');
        $stripLeading->setAccessible(true);
        $stripLeading->invokeArgs(null, [&$list]);
        $this->assertEquals(0, $list->count());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::litteralStripTrailing
     */
    public function testLitteralStripTrailing(): void
    {
        $list = new NodeList(new Blank('', 1));
        $list->push(new Blank('', 2));
        $stripTrailing = new \ReflectionMethod(Literals::class, 'litteralStripTrailing');
        $stripTrailing->setAccessible(true);
        $stripTrailing->invokeArgs(null, [&$list]);
        $this->assertEquals(0, $list->count());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::build
     */
    public function testBuild(): void
    {
        $this->assertEquals('', $this->nodeLiterals->build());
        //
        $nodeLit = new Literal("|", 42);
        $nodeLit->add(new Scalar('   sometext', 2));
        $nodeLit->add(new Scalar('   othertext', 2));
        $this->assertTrue($nodeLit->value instanceof NodeList);
        $this->assertTrue($nodeLit->value->offsetGet(0) instanceof Scalar);
        $this->assertEquals("sometext\nothertext\n", $nodeLit->build());
        // //
        $nodeLitClipped = new Literal("|-", 42);
        $nodeLitClipped->add(new Scalar('   sometext', 2));
        $nodeLitClipped->add(new Scalar('   othertext', 2));
        $this->assertTrue($nodeLitClipped->value instanceof NodeList);
        $this->assertTrue($nodeLitClipped->value->offsetGet(0) instanceof Scalar);
        $this->assertEquals("sometext\nothertext", $nodeLitClipped->build());
        //
        $nodeLitFolded = new LiteralFolded(">", 42);
        $nodeLitFolded->add(new Scalar('   sometext', 2));
        $nodeLitFolded->add(new Scalar('   othertext', 2));
        $this->assertTrue($nodeLitFolded->value instanceof NodeList);
        $this->assertTrue($nodeLitFolded->value->offsetGet(0) instanceof Scalar);
        $this->assertEquals("sometext othertext\n", $nodeLitFolded->build());
        // //
        $nodeLitFoldedClipped = new LiteralFolded(">-", 42);
        $nodeLitFoldedClipped->add(new Scalar('   sometext', 2));
        $nodeLitFoldedClipped->add(new Scalar('   othertext', 2));
        $this->assertTrue($nodeLitFoldedClipped->value instanceof NodeList);
        $this->assertTrue($nodeLitFoldedClipped->value->offsetGet(0) instanceof Scalar);
        $this->assertEquals("sometext othertext", $nodeLitFoldedClipped->build());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::getChildValue
     */
    public function testGetChildValue(): void
    {
        $getChildValue = new \ReflectionMethod($this->nodeLiterals, 'getChildValue');
        $getChildValue->setAccessible(true);
        $nodeQuoted = new Quoted('    "sometext"', 1);
        $nodeScalar = new Scalar('    sometext', 1);
        $nodeItem   = new Comment('    -  itemtext', 1);
        $nodeKey    = new Blank('    key: somevalue', 1);
        //
        $scalarResult = $getChildValue->invokeArgs($this->nodeLiterals, [$nodeScalar, 4]);
        $this->assertEquals('sometext', $scalarResult);
        //
        $keyResult = $getChildValue->invokeArgs($this->nodeLiterals, [$nodeKey, 4]);
        $this->assertEquals("", $keyResult);
        //
        $itemResult = $getChildValue->invokeArgs($this->nodeLiterals, [$nodeItem, 4]);
        // $this->assertEquals("-  itemtext\n", $itemResult);

        $quotedResult = $getChildValue->invokeArgs($this->nodeLiterals, [$nodeQuoted, 4]);
        $this->assertEquals("sometext", $quotedResult);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literals::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new Blank('', 1);
        $this->assertTrue($this->nodeLiterals->isAwaitingChild($uselessNode));
    }
}
