<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class BlankTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Blank
 */
class BlankTest extends TestCase
{
    /**
     * @var Blank $nodeBlank An instance of "Nodes\Blank" to test.
     */
    private $nodeBlank;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeBlank = new Blank('', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Blank::add
     */
    public function testAdd(): void
    {
        $nodeLit = new Literal('|', 1);
        $this->assertTrue(is_null($nodeLit->value));
        $nodeLit->add($this->nodeBlank);
        $this->assertTrue($nodeLit->value->offsetGet(0) === $this->nodeBlank);
        $nodeScalar = new Scalar('sometext', 3);
        $this->nodeBlank->add($nodeScalar);
        $this->assertTrue($nodeLit->value instanceof NodeList);
        $this->assertTrue($nodeLit->value->offsetGet(1) === $nodeScalar);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Blank::specialProcess
     */
    public function testSpecialProcess(): void
    {
        $blankBuffer = [];
        $previousScalarParent = new Literal('|-', 1);
        $previousScalar       = new Scalar('    sometext', 2);
        $previousScalarParent->add($previousScalar);
        $this->assertTrue($this->nodeBlank->specialProcess($previousScalar, $blankBuffer));
        $this->assertEquals($this->nodeBlank, $blankBuffer[0]);
        $this->assertEquals($this->nodeBlank->getParent(), $previousScalarParent);
        $blankBuffer = [];
        $keyNode = new Key(' somelit: |', 1);
        $this->assertTrue($this->nodeBlank->specialProcess($keyNode, $blankBuffer));
        $this->assertEquals($this->nodeBlank, $blankBuffer[0]);
        $this->assertEquals($this->nodeBlank->getParent(), $keyNode->value);

    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Blank::build
     */
    public function testBuild(): void
    {
        $this->assertEquals("\n", $this->nodeBlank->build());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Blank::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $uselessNode = new Scalar('sometext with no indent', 3);
        $blankBuffer = [];
        $keyNode = new Key(' somelit: |', 1);
        $keyNode->add($this->nodeBlank);
        $this->assertEquals($this->nodeBlank->getTargetOnEqualIndent($uselessNode), $keyNode);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Blank::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $uselessNode = new Scalar('sometext with no indent', 3);
        $blankBuffer = [];
        $keyNode = new Key(' somelit: |', 1);
        $keyNode->add($this->nodeBlank);
        $this->assertEquals($this->nodeBlank->getTargetOnMoreIndent($uselessNode), $keyNode);    }
}
