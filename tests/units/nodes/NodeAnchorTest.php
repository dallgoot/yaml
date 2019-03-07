<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\NodeAnchor;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeBlank;

/**
 * Class NodeAnchorTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeAnchor
 */
class NodeAnchorTest extends TestCase
{
    /**
     * @var NodeAnchor $nodeAnchor An instance of "NodeAnchor" to test.
     */
    private $nodeAnchor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeAnchor = new NodeAnchor('&aaa sometext', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeAnchor::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject();
        $rootNode   = new NodeRoot();
        $rootNode->add($this->nodeAnchor);
        $reflector  = new \ReflectionClass($rootNode);
        $buildFinal = $reflector->getMethod('buildFinal');
        $buildFinal->setAccessible(true);
        $buildFinal->invoke($rootNode, $yamlObject);
        $this->assertEquals('sometext', $this->nodeAnchor->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeAnchor::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $this->assertFalse($this->nodeAnchor->IsAwaitingChild($uselessNode));
        $this->nodeAnchor->value = null;
        $this->assertTrue($this->nodeAnchor->IsAwaitingChild($uselessNode));
    }
}
