<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Anchor;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Comment;
use Dallgoot\Yaml\Nodes\DocStart;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\LiteralFolded;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Nodes\Tag;

use Dallgoot\Yaml\Types\YamlObject;

/**
 * Class DocStartTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\DocStart
 */
class DocStartTest extends TestCase
{
    /**
     * @var DocStart $nodeDocStart An instance of "Nodes\DocStart" to test.
     */
    private $nodeDocStart;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeDocStart = new DocStart("---", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocStart::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue(is_null($this->nodeDocStart->value));
        $this->nodeDocStart = new DocStart("--- sometext", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof Scalar);
        $this->nodeDocStart = new DocStart("--- !sometag", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof Tag);
        $this->nodeDocStart = new DocStart("--- #some comment", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof Comment);
        $this->nodeDocStart = new DocStart("--- &someanchor", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof Anchor);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocStart::add
     */
    public function testAdd(): void
    {
        $blankNode = new Blank('', 2);
        $this->assertEquals($blankNode, $this->nodeDocStart->add($blankNode));
        $this->assertEquals($blankNode, $this->nodeDocStart->value);
        $nodeTag = new Tag('!tagname', 2);
        $this->nodeDocStart->value = null;
        $this->nodeDocStart->add($nodeTag);
        $this->assertEquals($nodeTag, $this->nodeDocStart->value);
        $this->nodeDocStart->add($blankNode);
        $this->assertEquals($nodeTag, $this->nodeDocStart->value);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocStart::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject(0);
        $this->assertTrue(is_null($this->nodeDocStart->build($yamlObject)));
        $this->nodeDocStart->add(new Tag('!<tag:clarkevans.com,2002:invoice>', 2));
        $this->nodeDocStart->build($yamlObject);
        $this->assertTrue($yamlObject->isTagged());
        $this->nodeDocStart->value = null;
        $nodeLit = new LiteralFolded('>-', 1);
        $nodeScalar1 = new Scalar('    some text', 2);
        $nodeScalar2 = new Scalar('    other text', 3);
        $nodeLit->add($nodeScalar1);
        $nodeLit->add($nodeScalar2);
        $this->nodeDocStart->add($nodeLit);
        $this->assertTrue(is_null($this->nodeDocStart->build($yamlObject)));
        $this->assertEquals("some text other text", ''.$yamlObject);

    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocStart::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new Blank('', 1);
        $this->assertFalse($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new Anchor('&anchor anchorDefinedWithValue', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new Literal('|+', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new LiteralFolded('>-', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocStart::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $blank = new Blank('', 1);
        $rootNode = new Root();
        $rootNode->add($this->nodeDocStart);
        $this->assertEquals($rootNode, $this->nodeDocStart->GetTargetOnEqualIndent($blank));

        $nodeLitFolded = new LiteralFolded('>-', 1);
        $this->nodeDocStart->value = $nodeLitFolded;
        $this->assertEquals($nodeLitFolded, $this->nodeDocStart->GetTargetOnEqualIndent($blank));
    }
}
