<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeComment;

/**
 * Class NodeScalarTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeScalar
 */
class NodeScalarTest extends TestCase
{
    /**
     * @var NodeScalar $nodeScalar An instance of "NodeScalar" to test.
     */
    private $nodeScalar;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeScalar = new NodeScalar("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::__construct
     */
    public function testConstruct(): void
    {
        // only a Sclar Node
        $this->assertTrue(is_null($this->nodeScalar->value));
        // with a comment
        $nodeScalar = new NodeScalar(' value # a comment', 1);
        $this->assertTrue($nodeScalar->value instanceof NodeList);
        $this->assertTrue($nodeScalar->value->offsetGet(0) instanceof NodeScalar);
        $this->assertTrue($nodeScalar->value->offsetGet(1) instanceof NodeComment);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::build
     */
    public function testBuild(): void
    {
        $this->assertEquals("a string to test", $this->nodeScalar->build());
        $this->nodeScalar->value = new NodeScalar('another string', 2);
        $this->assertEquals('another string', $this->nodeScalar->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::getTargetOnLessIndent
     */
    public function testGetTargetOnLessIndent(): void
    {
        $parent = new NodeKey('  emptykey: |', 1);
        $nodeScalar = new NodeScalar(' somestring', 2);
        $parent->add($this->nodeScalar);
        $this->assertEquals($parent, $this->nodeScalar->getTargetOnLessIndent($parent));
        $this->assertTrue($this->nodeScalar->getParent() instanceof NodeLit);
        //
        $parent2 = new NodeKey('  emptykey2:', 1);
        $this->nodeScalar = new NodeScalar('somestring', 2);
        $parent2->add($this->nodeScalar);
        $this->assertEquals($parent2, $this->nodeScalar->getTargetOnLessIndent($parent2));
        $this->assertEquals($parent2, $this->nodeScalar->getParent());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $parent = new NodeKey('  emptykey:', 1);
        $parent->add($this->nodeScalar);
        $this->assertEquals($parent, $this->nodeScalar->getTargetOnMoreIndent($parent));
    }
}
