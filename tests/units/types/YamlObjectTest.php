<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\YamlObject;

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
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::__call
     */
    public function testCall(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::__toString
     */
    public function testToString(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\YamlObject::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
