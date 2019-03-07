<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\API;

/**
 * Class YamlObjectTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\YamlObject
 */
class YamlObjectTest extends TestCase
{
    /**
     * @var YamlObject $yamlObject An instance of "YamlObject" to test.
     */
    private $yamlObject;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->yamlObject = new YamlObject();
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::__construct
     */
    public function testConstruct(): void
    {
        $reflector = new \ReflectionClass($this->yamlObject);
        $__yaml__object__api = $reflector->getProperty('__yaml__object__api');
        $__yaml__object__api->setAccessible(true);
        $this->yamlObject->__construct();
        $this->assertTrue($__yaml__object__api->getValue($this->yamlObject) instanceof API);
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::__call
     * @todo : test ALL API public methods ???
     */
    public function testCall(): void
    {
        $this->assertTrue(is_bool($this->yamlObject->hasDocStart()));
        $this->assertTrue(is_array($this->yamlObject->getComment()));
        $this->assertTrue(is_array($this->yamlObject->getAllReferences()));
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::__toString
     */
    public function testToString(): void
    {
        $this->yamlObject->setText('some text value');
        $this->assertTrue(is_string(''.$this->yamlObject));
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $this->assertEquals("_Empty YamlObject_", $this->yamlObject->jsonSerialize());
    }
}
