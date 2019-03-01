<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeDirective;
use Dallgoot\Yaml\Node;

/**
 * Class NodeDirectiveTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeDirective
 */
class NodeDirectiveTest extends TestCase
{
    /**
     * @var NodeDirective $nodeDirective An instance of "NodeDirective" to test.
     */
    private $nodeDirective;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeDirective = new NodeDirective();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDirective::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDirective::add
     */
    public function testAdd(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
