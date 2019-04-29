<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class LiteralTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Literal
 */
class LiteralTest extends TestCase
{
    /**
     * @var Literal $nodeLit An instance of "Nodes\Literal" to test.
     */
    private $nodeLit;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeLit = new Literal('|', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Literal::getFinalString
     */
    public function testGetFinalString(): void
    {
        $line1 = new Scalar('    some text', 2);
        $line2 = new Scalar('      some more indented text', 3);
        $line3 = new Scalar('    other less indented text', 4);
        $list = new NodeList;
        $list->push($line1);
        $list->push($line2);
        $list->push($line3);
        $this->assertEquals("some text\n  some more indented text\nother less indented text",
                            $this->nodeLit->getFinalString($list));
    }
}
