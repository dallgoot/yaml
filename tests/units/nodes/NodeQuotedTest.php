<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeQuoted;

/**
 * Class NodeQuotedTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeQuoted
 */
class NodeQuotedTest extends TestCase
{
    /**
     * @var NodeQuoted $nodeQuoted An instance of "NodeQuoted" to test.
     */
    private $nodeQuoted;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeQuoted = new NodeQuoted();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeQuoted::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
