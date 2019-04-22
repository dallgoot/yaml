<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeList;

/**
 * Class NodeBlankTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeBlank
 */
class NodeBlankTest extends TestCase
{
    /**
     * @var NodeBlank $nodeBlank An instance of "NodeBlank" to test.
     */
    private $nodeBlank;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeBlank = new NodeBlank('', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeBlank::add
     */
    public function testAdd(): void
    {
        $nodeLit = new NodeLit('|', 1);
        $this->assertTrue(is_null($nodeLit->value));
        $nodeLit->add($this->nodeBlank);
        $this->assertTrue($nodeLit->value->offsetGet(0) === $this->nodeBlank);
        $nodeScalar = new NodeScalar('sometext', 3);
        $this->nodeBlank->add($nodeScalar);
        $this->assertTrue($nodeLit->value instanceof NodeList);
        $this->assertTrue($nodeLit->value->offsetGet(1) === $nodeScalar);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeBlank::specialProcess
     */
    public function testSpecialProcess(): void
    {
        $blankBuffer = [];
        $previousScalarParent = new NodeLit('|-', 1);
        $previousScalar       = new NodeScalar('    sometext', 2);
        $previousScalarParent->add($previousScalar);
        $this->assertTrue($this->nodeBlank->specialProcess($previousScalar, $blankBuffer));
        $this->assertEquals($this->nodeBlank, $blankBuffer[0]);
        $this->assertEquals($this->nodeBlank->getParent(), $previousScalarParent);
        $blankBuffer = [];
        $keyNode = new NodeKey(' somelit: |', 1);
        $this->assertTrue($this->nodeBlank->specialProcess($keyNode, $blankBuffer));
        $this->assertEquals($this->nodeBlank, $blankBuffer[0]);
        $this->assertEquals($this->nodeBlank->getParent(), $keyNode->value);

    }

    /**
     * @covers \Dallgoot\Yaml\NodeBlank::build
     */
    public function testBuild(): void
    {
        $this->assertEquals("\n", $this->nodeBlank->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeBlank::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $uselessNode = new NodeScalar('sometext with no indent', 3);
        $blankBuffer = [];
        $keyNode = new NodeKey(' somelit: |', 1);
        $keyNode->add($this->nodeBlank);
        $this->assertEquals($this->nodeBlank->getTargetOnEqualIndent($uselessNode), $keyNode);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeBlank::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $uselessNode = new NodeScalar('sometext with no indent', 3);
        $blankBuffer = [];
        $keyNode = new NodeKey(' somelit: |', 1);
        $keyNode->add($this->nodeBlank);
        $this->assertEquals($this->nodeBlank->getTargetOnMoreIndent($uselessNode), $keyNode);    }
}
