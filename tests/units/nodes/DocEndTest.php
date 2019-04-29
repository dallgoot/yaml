<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\DocEnd;

/**
 * Class DocEndTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\DocEnd
 */
class DocEndTest extends TestCase
{
    /**
     * @var DocEnd $nodeDocEnd An instance of "Nodes\DocEnd" to test.
     */
    private $nodeDocEnd;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeDocEnd = new DocEnd('...');
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\DocEnd::build
     */
    public function testBuild(): void
    {
        $this->assertTrue(is_null($this->nodeDocEnd->build()));
    }
}
