<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeComment;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeLitFolded;
use Dallgoot\Yaml\NodeLiterals;
use Dallgoot\Yaml\NodeQuoted;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeLiteralsTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeLiterals
 */
class NodeLiteralsTest extends TestCase
{
    /**
     * @var NodeLiterals $nodeLiterals An instance of "NodeLiterals" to test.
     */
    private $nodeLiterals;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs(["|-", 42])
            ->getMockForAbstractClass();
    }

    /**
    //  * @covers \Dallgoot\Yaml\NodeLiterals::getFinalString
    //  */
    // public function testGetFinalString(): void
    // {
    //     /** @todo Complete this unit test method. */
    //     $this->markTestIncomplete();
    // }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::__construct
     */
    public function testConstruct(): void
    {
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs(["|", 42])
            ->getMockForAbstractClass();
        $reflector = new \ReflectionClass($this->nodeLiterals);//var_dump($this->nodeLiterals);
        $identifier = $reflector->getProperty('identifier');
        $identifier->setAccessible(true);
        $this->assertEquals(null, $identifier->getValue($this->nodeLiterals));
        //
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs(["|-", 42])
            ->getMockForAbstractClass();
        //
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs(["|+", 42])
            ->getMockForAbstractClass();
        $this->assertEquals('+', $identifier->getValue($this->nodeLiterals));
        //
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs([">-", 42])
            ->getMockForAbstractClass();
        //
        $this->nodeLiterals = $this->getMockBuilder(NodeLiterals::class)
            ->setConstructorArgs([">+", 42])
            ->getMockForAbstractClass();
        $this->assertEquals('+', $identifier->getValue($this->nodeLiterals));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::add
     */
    public function testAdd(): void
    {
        $this->assertTrue(is_null($this->nodeLiterals->value));
        $this->nodeLiterals->add(new NodeScalar(' some text', 2));
        $this->assertTrue($this->nodeLiterals->value instanceof NodeList);
        //
        $this->nodeLiterals->value = null;
        $falseItem = new NodeItem(' - not an item', 2);

        $this->assertTrue($this->nodeLiterals->add($falseItem) instanceof NodeScalar);
        $this->assertFalse($this->nodeLiterals->value->offsetGet(0) instanceof NodeItem);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::litteralStripLeading
     */
    public function testLitteralStripLeading(): void
    {
        $list = new NodeList(new NodeBlank('', 1));
        $list->push(new NodeBlank('', 2));
        $stripLeading = new \ReflectionMethod(NodeLiterals::class, 'litteralStripLeading');
        $stripLeading->setAccessible(true);
        $stripLeading->invokeArgs(null, [&$list]);
        $this->assertEquals(0, $list->count());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::litteralStripTrailing
     */
    public function testLitteralStripTrailing(): void
    {
        $list = new NodeList(new NodeBlank('', 1));
        $list->push(new NodeBlank('', 2));
        $stripTrailing = new \ReflectionMethod(NodeLiterals::class, 'litteralStripTrailing');
        $stripTrailing->setAccessible(true);
        $stripTrailing->invokeArgs(null, [&$list]);
        $this->assertEquals(0, $list->count());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::build
     */
    public function testBuild(): void
    {
        $this->assertEquals('', $this->nodeLiterals->build());
        //
        $nodeLit = new NodeLit("|", 42);
        $nodeLit->add(new NodeScalar('   sometext', 2));
        $nodeLit->add(new NodeScalar('   othertext', 2));
        $this->assertTrue($nodeLit->value instanceof NodeList);
        $this->assertTrue($nodeLit->value->offsetGet(0) instanceof NodeScalar);
        $this->assertEquals("sometext\nothertext\n", $nodeLit->build());
        // //
        $nodeLitClipped = new NodeLit("|-", 42);
        $nodeLitClipped->add(new NodeScalar('   sometext', 2));
        $nodeLitClipped->add(new NodeScalar('   othertext', 2));
        $this->assertTrue($nodeLitClipped->value instanceof NodeList);
        $this->assertTrue($nodeLitClipped->value->offsetGet(0) instanceof NodeScalar);
        $this->assertEquals("sometext\nothertext", $nodeLitClipped->build());
        //
        $nodeLitFolded = new NodeLitFolded(">", 42);
        $nodeLitFolded->add(new NodeScalar('   sometext', 2));
        $nodeLitFolded->add(new NodeScalar('   othertext', 2));
        $this->assertTrue($nodeLitFolded->value instanceof NodeList);
        $this->assertTrue($nodeLitFolded->value->offsetGet(0) instanceof NodeScalar);
        $this->assertEquals("sometext othertext\n", $nodeLitFolded->build());
        // //
        $nodeLitFoldedClipped = new NodeLitFolded(">-", 42);
        $nodeLitFoldedClipped->add(new NodeScalar('   sometext', 2));
        $nodeLitFoldedClipped->add(new NodeScalar('   othertext', 2));
        $this->assertTrue($nodeLitFoldedClipped->value instanceof NodeList);
        $this->assertTrue($nodeLitFoldedClipped->value->offsetGet(0) instanceof NodeScalar);
        $this->assertEquals("sometext othertext", $nodeLitFoldedClipped->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLiterals::getChildValue
     */
    public function testGetChildValue(): void
    {
        $getChildValue = new \ReflectionMethod($this->nodeLiterals, 'getChildValue');
        $getChildValue->setAccessible(true);
        $nodeQuoted = new NodeQuoted('    "sometext"', 1);
        $nodeScalar = new NodeScalar('    sometext', 1);
        $nodeItem   = new NodeComment('    -  itemtext', 1);
        $nodeKey    = new NodeBlank('    key: somevalue', 1);
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
     * @covers \Dallgoot\Yaml\NodeLiterals::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $this->assertTrue($this->nodeLiterals->isAwaitingChild($uselessNode));
    }
}
