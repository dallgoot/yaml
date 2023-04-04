<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Comment;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class ScalarTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Scalar
 */
class ScalarTest extends TestCase
{
    /**
     * @var Scalar $nodeScalar An instance of "Nodes\Scalar" to test.
     */
    private $nodeScalar;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeScalar = new Scalar("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::__construct
     */
    public function testConstruct(): void
    {
        // only a Sclar Node
        $this->assertTrue(is_null($this->nodeScalar->value));
        // with a comment
        $nodeScalar = new Scalar(' value # a comment', 1);
        $this->assertTrue($nodeScalar->value instanceof NodeList);
        $this->assertTrue($nodeScalar->value->offsetGet(0) instanceof Scalar);
        $this->assertTrue($nodeScalar->value->offsetGet(1) instanceof Comment);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::build
     */
    public function testBuild(): void
    {
        $this->assertEquals("a string to test", $this->nodeScalar->build());
        //
        $this->nodeScalar->value = new Scalar('another string', 2);
        $this->assertEquals('another string', $this->nodeScalar->build());
        //
        $this->nodeScalar->raw = "123";
        $this->nodeScalar->value = null;
        $this->assertEquals(123, $this->nodeScalar->build());
    }

        /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::build
     */
    public function testBuildTagged(): void
    {
        $this->nodeScalar = new Scalar("123", 42);
        $this->nodeScalar->tag = '!!str';
        $this->assertEquals('123', $this->nodeScalar->build());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::getTargetOnLessIndent
     */
    public function testGetTargetOnLessIndent(): void
    {
        $parent = new Key('  emptykey: |', 1);
        $nodeScalar = new Scalar(' somestring', 2);
        $parent->add($this->nodeScalar);
        $this->assertEquals($parent, $this->nodeScalar->getTargetOnLessIndent($parent));
        $this->assertTrue($this->nodeScalar->getParent() instanceof Literal);
        //
        $parent2 = new Key('  emptykey2:', 1);
        $this->nodeScalar = new Scalar('somestring', 2);
        $parent2->add($this->nodeScalar);
        $blankNode = new Blank('', 3);
        $this->assertEquals($parent2, $this->nodeScalar->getTargetOnLessIndent($blankNode));
        $this->assertEquals($parent2, $this->nodeScalar->getParent());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $parent = new Key('  emptykey:', 1);
        $parent->add($this->nodeScalar);
        $this->assertEquals($parent, $this->nodeScalar->getTargetOnMoreIndent($parent));
    }

        /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::getScalar
     */
    public function testGetScalar(): void
    {
        $this->assertEquals($this->nodeScalar->getScalar('yes')  , true);
        $this->assertEquals($this->nodeScalar->getScalar('no')   , false);
        $this->assertEquals($this->nodeScalar->getScalar('true') , true);
        $this->assertEquals($this->nodeScalar->getScalar('false'), false);
        $this->assertEquals($this->nodeScalar->getScalar('null') , null);
        $this->assertEquals($this->nodeScalar->getScalar('.inf') , \INF);
        $this->assertEquals($this->nodeScalar->getScalar('-.inf'), -\INF);
        $this->assertTrue(is_nan($this->nodeScalar->getScalar('.nan')));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Scalar::getNumber
     */
    public function testGetNumber(): void
    {
        $reflector = new \ReflectionClass($this->nodeScalar);
        $method = $reflector->getMethod('getNumber');
        $method->setAccessible(true);
        $this->assertTrue(is_numeric($method->invoke($this->nodeScalar, '132')));
        $this->assertTrue(is_numeric($method->invoke($this->nodeScalar, '0x27')));
        $this->assertTrue(is_numeric($method->invoke($this->nodeScalar, '0xaf')));
        $this->assertTrue(is_float($method->invoke($this->nodeScalar, '132.123')));
        $this->assertFalse(is_float($method->invoke($this->nodeScalar, '132.12.3')));
    }
}
