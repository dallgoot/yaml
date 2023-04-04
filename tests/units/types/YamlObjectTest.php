<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\YamlProperties;
use ReflectionProperty;

/**
 * Class YamlObjectTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Types\YamlObject
 */
class YamlObjectTest extends TestCase
{
    /**
     * @var YamlObject $yamlObject An instance of "YamlObject" to test.
     */
    private $yamlObject;

    private $refValue = 123;
    private $commentValue = '# this a full line comment';
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->yamlObject = new YamlObject(0);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::__construct
     */
    public function testConstruct(): void
    {
        $reflector = new \ReflectionClass($this->yamlObject);
        $__yaml__object__api = $reflector->getProperty('__yaml__object__api');
        $__yaml__object__api->setAccessible(true);
        $this->yamlObject->__construct(0);
        $this->assertTrue($__yaml__object__api->getValue($this->yamlObject) instanceof YamlProperties);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::__toString
     */
    public function testToString(): void
    {
        $this->yamlObject->setText('some text value');
        $this->assertTrue(is_string(''.$this->yamlObject));
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $this->assertEquals("_Empty YamlObject_", $this->yamlObject->jsonSerialize());
    }

        /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::addReference
     */
    public function testAddReference(): void
    {
        $this->assertEquals($this->yamlObject->getAllReferences(), []);
        $this->yamlObject->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->yamlObject->getReference('referenceName'), $this->refValue);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::getReference
     */
    public function testGetReference(): void
    {
        $this->assertEquals($this->yamlObject->getAllReferences(), []);
        $this->yamlObject->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->yamlObject->getReference('referenceName'), $this->refValue);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::getAllReferences
     */
    public function testGetAllReferences(): void
    {
        $this->assertEquals($this->yamlObject->getAllReferences(), []);
        $this->yamlObject->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->yamlObject->getAllReferences(), ['referenceName' => $this->refValue]);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::addComment
     */
    public function testAddComment(): void
    {
        $this->assertEquals($this->yamlObject->getComment(), []);
        $this->yamlObject->addComment(20, $this->commentValue);
        $this->assertEquals($this->yamlObject->getComment(20), $this->commentValue);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::getComment
     * @depends testAddComment
     */
    public function testGetComment(): void
    {
        $this->assertEquals($this->yamlObject->getComment(), []);
        $this->yamlObject->addComment(20, $this->commentValue);
        $this->assertEquals($this->yamlObject->getComment(20), $this->commentValue);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::setText
     */
    public function testSetText(): void
    {
        $container = new ReflectionProperty($this->yamlObject, '__yaml__object__api');
        $container->setAccessible(true);
        $this->assertTrue(is_null($container->getValue($this->yamlObject)->value));
        $txt = '      a  text with leading spaces';
        $yamlObject = $this->yamlObject->setText($txt);
        $this->assertTrue($container->getValue($this->yamlObject)->value === ltrim($txt));
        $this->assertTrue($yamlObject instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::addTag
     */
    public function testAddTag(): void
    {
        $this->assertFalse($this->yamlObject->isTagged());
        $this->yamlObject->addTag('!', 'tag:clarkevans.com,2002');
        $this->assertTrue($this->yamlObject->isTagged());
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::hasDocStart
     */
    public function testHasDocStart(): void
    {
        $this->assertFalse($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(false);
        $this->assertTrue($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(true);
        $this->assertTrue($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(null);
        $this->assertFalse($this->yamlObject->hasDocStart());
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::setDocStart
     */
    public function testSetDocStart(): void
    {
        $this->assertFalse($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(false);
        $this->assertTrue($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(true);
        $this->assertTrue($this->yamlObject->hasDocStart());
        $this->yamlObject->setDocStart(null);
        $this->assertFalse($this->yamlObject->hasDocStart());
    }

    /**
     * @covers \Dallgoot\Yaml\Types\YamlObject::isTagged
     */
    public function testIsTagged(): void
    {
        $this->assertFalse($this->yamlObject->isTagged());
        $this->yamlObject->addTag('!', 'tag:clarkevans.com,2002');
        $this->assertTrue($this->yamlObject->isTagged());
    }
}
