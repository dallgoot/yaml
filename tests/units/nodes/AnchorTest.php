<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\YamlObject;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Anchor;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Root;

/**
 * Class AnchorTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Anchor
 */
class AnchorTest extends TestCase
{
    /**
     * @var Anchor $nodeAnchor An instance of "Nodes\Anchor" to test.
     */
    private $nodeAnchor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeAnchor = new Anchor('&aaa sometext', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Anchor::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject(0);
        $rootNode   = new Root();
        $rootNode->add($this->nodeAnchor);
        $reflector  = new \ReflectionClass($rootNode);
        $buildFinal = $reflector->getMethod('buildFinal');
        $buildFinal->setAccessible(true);
        $buildFinal->invoke($rootNode, $yamlObject);
        $this->assertEquals('sometext', $this->nodeAnchor->build());
        // test exsting reference
        $anchorValue = '12345';
        $yamlObject = new YamlObject(0);
        $rootNode   = new Root();
        $this->nodeAnchor = new Anchor('*aaa', 1);

        $rootNode->add($this->nodeAnchor);
        $yamlObject->addReference('aaa', $anchorValue);
        $buildFinal  = new \ReflectionMethod($rootNode, 'buildFinal');
        $buildFinal->setAccessible(true);
        $buildFinal->invoke($rootNode, $yamlObject);
        $this->assertEquals('12345', $this->nodeAnchor->build());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Anchor::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new Blank('', 1);
        $this->assertFalse($this->nodeAnchor->IsAwaitingChild($uselessNode));
        $this->nodeAnchor->value = null;
        $this->assertTrue($this->nodeAnchor->IsAwaitingChild($uselessNode));
    }
}
