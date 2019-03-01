<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeCompactSequence;

/**
 * Class NodeCompactSequenceTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeCompactSequence
 */
class NodeCompactSequenceTest extends TestCase
{
    /**
     * @var NodeCompactSequence $nodeCompactSequence An instance of "NodeCompactSequence" to test.
     */
    private $nodeCompactSequence;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeCompactSequence = new NodeCompactSequence("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactSequence::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactSequence::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
