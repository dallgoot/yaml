<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\LiteralFolded;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class LiteralFoldedTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\LiteralFolded
 */
class LiteralFoldedTest extends TestCase
{
    /**
     * @var LiteralFolded $nodeLitFolded An instance of "Nodes\LiteralFolded" to test.
     */
    private $nodeLitFolded;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeLitFolded = new LiteralFolded('>-', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\LiteralFolded::getFinalString
     */
    public function testGetFinalString(): void
    {
        $line1  = new Scalar(' - with inside', 2);
        $line1a = new Scalar('    two', 3);
        $line1b = new Scalar('    children', 4);
        $line2  = new Scalar('      some more indented text', 5);
        $line3  = new Scalar('    other less indented text', 6);
        $list = new NodeList;
        $list->push($line1);
        $list->push($line1a);
        $list->push($line1b);
        $list->push($line2);
        $list->push($line3);
        $this->assertEquals(
            "- with inside\ntwo\nchildren\nsome more indented text\nother less indented text",
            $this->nodeLitFolded->getFinalString($list));
    }
}
