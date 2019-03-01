<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeAnchor;
use Dallgoot\Yaml\Node;

/**
 * Class NodeAnchorTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeAnchor
 */
class NodeAnchorTest extends TestCase
{
    /**
     * @var NodeAnchor $nodeAnchor An instance of "NodeAnchor" to test.
     */
    private $nodeAnchor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeAnchor = new NodeAnchor();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeAnchor::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeAnchor::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
