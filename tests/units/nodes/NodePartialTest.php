<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodePartial;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeQuoted;

/**
 * Class NodePartialTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodePartial
 */
class NodePartialTest extends TestCase
{
    /**
     * @var NodePartial $nodePartial An instance of "NodePartial" to test.
     */
    private $nodePartial;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodePartial = new NodePartial(' " partially quoted');
    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::specialProcess
     */
    public function testSpecialProcess(): void
    {
        $blankBuffer = [];
        $node = new NodeScalar(' end of quoting"', 2);
        $parent = new NodeScalar(' emptykey:', 1);
        $parent->add($this->nodePartial);
        $this->assertTrue($this->nodePartial->specialProcess($node, $blankBuffer));
        $this->assertTrue($parent->value instanceof NodeQuoted);
        $this->assertEquals(" partially quoted end of quoting", $parent->value->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::specialProcess
     */
    public function testSpecialProcessWithPreviousLineFeed(): void
    {
        $this->nodePartial = new NodePartial(" ' partially quoted\n");
        $blankBuffer = [];
        $node = new NodeScalar(" end of quoting'", 2);
        $parent = new NodeScalar(' emptykey:', 1);
        $parent->add($this->nodePartial);
        $this->assertTrue($this->nodePartial->specialProcess($node, $blankBuffer));
        $this->assertTrue($parent->value instanceof NodeQuoted);
        $this->assertEquals(" partially quoted\nend of quoting", $parent->value->build());

    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::specialProcess
     */
    public function testSpecialProcessWithNodeBlank(): void
    {
        $blankBuffer = [];
        $current = new NodeBlank('', 2);
        $parent = new NodeScalar(' emptykey:', 1);
        $parent->add($this->nodePartial);
        $this->assertTrue($this->nodePartial->specialProcess($current, $blankBuffer));
        $this->assertTrue($parent->value instanceof NodePartial);
        $this->assertEquals(" \" partially quoted\n", $parent->value->raw);
    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::build
     */
    public function testBuild(): void
    {
        $this->expectException(\ParseError::class);
        $this->nodePartial->build();
    }
}
