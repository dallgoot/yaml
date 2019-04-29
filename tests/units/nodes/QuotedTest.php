<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\Quoted;

/**
 * Class QuotedTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Quoted
 */
class QuotedTest extends TestCase
{
    /**
     * @var Quoted $nodeQuoted An instance of "Nodes\Quoted" to test.
     */
    private $nodeQuoted;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeQuoted = new Quoted('"a quoted string"');
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Quoted::build
     */
    public function testBuild(): void
    {
        $this->assertEquals("a quoted string", $this->nodeQuoted->build());
    }
}
