<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeLitFolded;
use Dallgoot\Yaml\NodeList;

/**
 * Class NodeLitFoldedTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeLitFolded
 */
class NodeLitFoldedTest extends TestCase
{
    /**
     * @var NodeLitFolded $nodeLitFolded An instance of "NodeLitFolded" to test.
     */
    private $nodeLitFolded;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeLitFolded = new NodeLitFolded('>-', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLitFolded::getFinalString
     */
    public function testGetFinalString(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
