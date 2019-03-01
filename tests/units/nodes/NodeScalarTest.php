<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\Node;

/**
 * Class NodeScalarTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeScalar
 */
class NodeScalarTest extends TestCase
{
    /**
     * @var NodeScalar $nodeScalar An instance of "NodeScalar" to test.
     */
    private $nodeScalar;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeScalar = new NodeScalar("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::getTargetOnLessIndent
     */
    public function testGetTargetOnLessIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeScalar::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
