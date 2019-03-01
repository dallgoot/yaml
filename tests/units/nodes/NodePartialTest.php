<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodePartial;
use Dallgoot\Yaml\Node;

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
        /** @todo Maybe add some arguments to this constructor */
        $this->nodePartial = new NodePartial();
    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::specialProcess
     */
    public function testSpecialProcess(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodePartial::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
