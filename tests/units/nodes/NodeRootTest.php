<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeRootTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeRoot
 */
class NodeRootTest extends TestCase
{
    /**
     * @var NodeRoot $nodeRoot An instance of "NodeRoot" to test.
     */
    private $nodeRoot;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeRoot = new NodeRoot();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeRoot->value instanceof NodeList);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getParent
     */
    public function testGetParent(): void
    {
        $this->assertEquals($this->nodeRoot, $this->nodeRoot->getParent());
        $keyNode = new NodeKey(' falsekey:', 1);
        $keyNode->add($this->nodeRoot);
        $this->expectException(\ParseError::class);
        $this->nodeRoot->getParent();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getRoot
     */
    public function testGetRoot(): void
    {
        $this->assertEquals($this->nodeRoot, $this->nodeRoot->getRoot());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getYamlObject
     */
    public function testYamlObjectAbsent()
    {
        $this->expectException(\Exception::class);
        $this->nodeRoot->getYamlObject();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getYamlObject
     * @depends testBuildFinal
     */
    public function testYamlObjectPresent()
    {
        $buildFinal = new \ReflectionMethod($this->nodeRoot, 'buildFinal');
        $buildFinal->setAccessible(true);
        $yamlObject = new YamlObject;
        $result = $buildFinal->invoke($this->nodeRoot, $yamlObject);

        $this->assertEquals($yamlObject, $result);
        $this->assertEquals($yamlObject, $this->nodeRoot->getYamlObject());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject;
        $this->assertTrue($this->nodeRoot->build($yamlObject) instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::buildFinal
     */
    public function testBuildFinal(): void
    {
        $buildFinal = new \ReflectionMethod($this->nodeRoot, 'buildFinal');
        $buildFinal->setAccessible(true);
        $yamlObject = new YamlObject;
        $result = $buildFinal->invoke($this->nodeRoot, $yamlObject);
        $this->assertEquals($yamlObject, $result);
    }
}
