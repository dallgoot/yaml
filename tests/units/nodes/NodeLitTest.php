<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeList;

/**
 * Class NodeLitTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeLit
 */
class NodeLitTest extends TestCase
{
    /**
     * @var NodeLit $nodeLit An instance of "NodeLit" to test.
     */
    private $nodeLit;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeLit = new NodeLit();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLit::getFinalString
     */
    public function testGetFinalString(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
