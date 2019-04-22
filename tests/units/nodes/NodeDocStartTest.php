<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeDocStart;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeAnchor;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeComment;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeLitFolded;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeTag;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeDocStartTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeDocStart
 */
class NodeDocStartTest extends TestCase
{
    /**
     * @var NodeDocStart $nodeDocStart An instance of "NodeDocStart" to test.
     */
    private $nodeDocStart;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeDocStart = new NodeDocStart("---", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue(is_null($this->nodeDocStart->value));
        $this->nodeDocStart = new NodeDocStart("--- sometext", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof NodeScalar);
        $this->nodeDocStart = new NodeDocStart("--- !sometag", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof NodeTag);
        $this->nodeDocStart = new NodeDocStart("--- #some comment", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof NodeComment);
        $this->nodeDocStart = new NodeDocStart("--- &someanchor", 42);
        $this->assertTrue($this->nodeDocStart->value instanceof NodeAnchor);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::add
     */
    public function testAdd(): void
    {
        $blankNode = new NodeBlank('', 2);
        $this->assertEquals($blankNode, $this->nodeDocStart->add($blankNode));
        $this->assertEquals($blankNode, $this->nodeDocStart->value);
        $nodeTag = new NodeTag('!tagname', 2);
        $this->nodeDocStart->value = null;
        $this->nodeDocStart->add($nodeTag);
        $this->assertEquals($nodeTag, $this->nodeDocStart->value);
        $this->nodeDocStart->add($blankNode);
        $this->assertEquals($nodeTag, $this->nodeDocStart->value);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject;
        $this->assertTrue(is_null($this->nodeDocStart->build($yamlObject)));
        $this->nodeDocStart->add(new NodeTag('!tagname', 2));
        $this->nodeDocStart->build($yamlObject);
        $this->assertTrue($yamlObject->isTagged());
        $this->nodeDocStart->value = null;
        $nodeLit = new NodeLitFolded('>-', 1);
        $nodeScalar1 = new NodeScalar('    some text', 2);
        $nodeScalar2 = new NodeScalar('    other text', 3);
        $nodeLit->add($nodeScalar1);
        $nodeLit->add($nodeScalar2);
        $this->nodeDocStart->add($nodeLit);
        $this->assertTrue(is_null($this->nodeDocStart->build($yamlObject)));
        $this->assertEquals("some text other text", ''.$yamlObject);

    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $this->assertFalse($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new NodeAnchor('&anchor anchorDefinedWithValue', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new NodeLit('|+', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));

        $this->nodeDocStart->value = new NodeLitFolded('>-', 1);
        $this->assertTrue($this->nodeDocStart->isAwaitingChild($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocStart::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $blank = new NodeBlank('', 1);
        $rootNode = new NodeRoot();
        $rootNode->add($this->nodeDocStart);
        $this->assertEquals($rootNode, $this->nodeDocStart->GetTargetOnEqualIndent($blank));

        $nodeLitFolded = new NodeLitFolded('>-', 1);
        $this->nodeDocStart->value = $nodeLitFolded;
        $this->assertEquals($nodeLitFolded, $this->nodeDocStart->GetTargetOnEqualIndent($blank));
    }
}
