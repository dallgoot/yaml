<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Directive;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Tag\TagFactory;
use Dallgoot\Yaml\Types\YamlObject;

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
        $this->nodeDirective = new Directive('%TAG ! tag:clarkevans.com,2002:');
        $rootNode = new Root;
        $rootNode->add($this->nodeDirective);
        $yamlObject = new YamlObject(0);
        $rootNode->build($yamlObject);
        $this->assertEquals('tag:clarkevans.com,2002:', TagFactory::$schemaHandles['!']);
    }

    public function testBuildError(): void
    {
        $this->expectException(\ParseError::class);
        $this->nodeDirective = new Directive('%TAG ! tag:clarkevans.com,2002:');
        $directive2 = new Directive('%TAG ! tag:clarkevans.com,2002:');
        $rootNode = new Root;
        $rootNode->add($this->nodeDirective);
        $yamlObject = new YamlObject(0);
        $rootNode->build($yamlObject);
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
