<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeActions;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeTag;

/**
 * Class NodeActionsTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeActions
 */
class NodeActionsTest extends TestCase
{
    /**
     * @var NodeActions $nodeActions An instance of "NodeActions" to test.
     */
    private $nodeActions;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeActions = new NodeActions("   !!str    sometext", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeActions::__construct
     */
    public function testConstruct(): void
    {
        //note : true behaviour for Tag is "testConstructWithTag"
        $this->assertEquals("!!str", $this->nodeActions->anchor);
        $this->assertTrue($this->nodeActions->value instanceof NodeScalar);
        $this->assertEquals("sometext", $this->nodeActions->value->raw);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeActions::__construct
     */
    public function testConstructWithTag(): void
    {
        $tagNode = new NodeTag("   !!str    sometext", 42);
        $this->assertEquals("!!str", $tagNode->tag);
        $this->assertTrue($tagNode->value instanceof NodeScalar);
        $this->assertEquals("sometext", $tagNode->value->raw);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeActions::build
     */
    public function testBuild(): void
    {
        $this->assertEquals(null, $this->nodeActions->build() );
    }
}