<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeTag;
use Dallgoot\Yaml\Node;

/**
 * Class NodeTagTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeTag
 */
class NodeTagTest extends TestCase
{
    /**
     * @var NodeTag $nodeTag An instance of "NodeTag" to test.
     */
    private $nodeTag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeTag = new NodeTag('!!str 654',1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
