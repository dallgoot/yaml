<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeLitTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
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
        $this->nodeLit = new NodeLit('|', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeLit::getFinalString
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
        $this->assertEquals("some text\n  some more indented text\nother less indented text",
                            $this->nodeLit->getFinalString($list));
    }
}
