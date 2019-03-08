<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeLitFolded;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeScalar;

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
        $line1 = new NodeScalar('    some text', 2);
        $line2 = new NodeScalar('      some more indented text', 3);
        $line3 = new NodeScalar('    other less indented text', 4);
        $list = new NodeList;
        $list->push($line1);
        $list->push($line2);
        $list->push($line3);
        $this->assertEquals("some text\nsome more indented text other less indented text",
                            $this->nodeLitFolded->getFinalString($list));
    }
}
