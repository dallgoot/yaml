<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Directive;
use Dallgoot\Yaml\Nodes\Blank;

/**
 * Class DirectiveTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Directive
 */
class DirectiveTest extends TestCase
{
    /**
     * @var NodeDirective $nodeDirective An instance of "Nodes\Directive" to test.
     */
    private $nodeDirective;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeDirective = new Directive('%YAML 1.2');
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Directive::build
     */
    public function testBuild(): void
    {
        $this->assertTrue(is_null($this->nodeDirective->build()));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Directive::add
     */
    public function testAdd(): void
    {
        $uselessNode = new Blank('', 2);
        $this->assertTrue($this->nodeDirective->add($uselessNode) === $uselessNode);
    }
}
