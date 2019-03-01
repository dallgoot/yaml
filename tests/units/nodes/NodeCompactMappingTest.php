<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeCompactMapping;

/**
 * Class NodeCompactMappingTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeCompactMapping
 */
class NodeCompactMappingTest extends TestCase
{
    /**
     * @var NodeCompactMapping $nodeCompactMapping An instance of "NodeCompactMapping" to test.
     */
    private $nodeCompactMapping;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeCompactMapping = new NodeCompactMapping("a string to test", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactMapping::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactMapping::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
