<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\Actions;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Nodes\Tag;

/**
 * Class ActionsTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Actions
 */
class ActionsTest extends TestCase
{
    /**
     * @var Actions $nodeActions An instance of "Nodes\Actions" to test.
     */
    private $nodeActions;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        // $this->nodeActions = new Actions("   !!str    sometext", 42);
        $this->nodeActions = $this->getMockBuilder(Actions::class)
                            ->setConstructorArgs(["   !!str    sometext", 42])
                            ->getMockForAbstractClass();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Actions::__construct
     */
    public function testConstruct(): void
    {
        //note : true behaviour for Tag is "testConstructWithTag"
        $this->assertEquals("!!str", $this->nodeActions->anchor);
        $this->assertTrue($this->nodeActions->value instanceof Scalar);
        $this->assertEquals("sometext", $this->nodeActions->value->raw);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Actions::__construct
     */
    public function testConstructWithTag(): void
    {
        $tagNode = new Tag("   !!str    sometext", 42);
        $this->assertEquals("!!str", $tagNode->tag);
        $this->assertTrue($tagNode->value instanceof Scalar);
        $this->assertEquals("sometext", $tagNode->value->raw);
    }

}