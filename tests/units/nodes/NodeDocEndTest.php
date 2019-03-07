<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeDocEnd;

/**
 * Class NodeDocEndTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeDocEnd
 */
class NodeDocEndTest extends TestCase
{
    /**
     * @var NodeDocEnd $nodeDocEnd An instance of "NodeDocEnd" to test.
     */
    private $nodeDocEnd;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeDocEnd = new NodeDocEnd('...');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeDocEnd::build
     */
    public function testBuild(): void
    {
        $this->assertTrue(is_null($this->nodeDocEnd->build()));
    }
}
