<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Key;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\YamlObject;

/**
 * Class RootTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Root
 */
class RootTest extends TestCase
{
    /**
     * @var Root $nodeRoot An instance of "Nodes\Root" to test.
     */
    private $nodeRoot;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeRoot = new Root();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeRoot->value instanceof NodeList);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::getParent
     */
    public function testGetParent(): void
    {
        $this->assertEquals($this->nodeRoot, $this->nodeRoot->getParent());
        $keyNode = new Key(' falsekey:', 1);
        $keyNode->add($this->nodeRoot);
        $this->expectException(\ParseError::class);
        $this->nodeRoot->getParent();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::getRoot
     */
    public function testGetRoot(): void
    {
        $this->assertEquals($this->nodeRoot, $this->nodeRoot->getRoot());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::getYamlObject
     */
    public function testYamlObjectAbsent()
    {
        $this->expectException(\Exception::class);
        $this->nodeRoot->getYamlObject();
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::buildFinal
     */
    public function testBuildFinal(): void
    {
        $buildFinal = new \ReflectionMethod($this->nodeRoot, 'buildFinal');
        $buildFinal->setAccessible(true);
        $yamlObject = new YamlObject(0);
        $result = $buildFinal->invoke($this->nodeRoot, $yamlObject);
        $this->assertEquals($yamlObject, $result);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::getYamlObject
     * @depends testBuildFinal
     */
    public function testYamlObjectPresent()
    {
        $buildFinal = new \ReflectionMethod($this->nodeRoot, 'buildFinal');
        $buildFinal->setAccessible(true);
        $yamlObject = new YamlObject(0);
        $result = $buildFinal->invoke($this->nodeRoot, $yamlObject);

        $this->assertEquals($yamlObject, $result);
        $this->assertEquals($yamlObject, $this->nodeRoot->getYamlObject());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Root::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject(0);
        $this->assertTrue($this->nodeRoot->build($yamlObject) instanceof YamlObject);
    }

}
