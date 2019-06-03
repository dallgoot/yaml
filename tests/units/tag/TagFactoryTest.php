<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Tag\CoreSchema;


/**
 * Class TagFactoryTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\TagFactory
 */
class TagFactoryTest extends TestCase
{
    /**
     * @var TagFactory $tagFactory An instance of "TagFactory" to test.
     */
    private $tagFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->tagFactory = new TagFactory();
    }

    public function testCreateCoreSchema()
    {
        $this->tagFactory::$schemas = [];
        $this->tagFactory::$schemaHandles = [];
        $createCoreSchema = new \ReflectionMethod($this->tagFactory, 'createCoreSchema');
        $createCoreSchema->setAccessible(true);
        $createCoreSchema->invoke(null);
        $this->assertArrayHasKey('!!', $this->tagFactory::$schemaHandles);
        $this->assertArrayHasKey(CoreSchema::SCHEMA_URI, $this->tagFactory::$schemas);
        $this->assertTrue($this->tagFactory::$schemas[CoreSchema::SCHEMA_URI] instanceof CoreSchema);
    }

    public function testRegisterSchema()
    {
        $coreSchema = new CoreSchema;
        $this->tagFactory::registerSchema($coreSchema::SCHEMA_URI, $coreSchema);
        $this->assertArrayHasKey(CoreSchema::SCHEMA_URI, $this->tagFactory::$schemas);
    }

    public function testRegisterHandle()
    {
        $coreSchema = new CoreSchema;
        $this->tagFactory::registerHandle("!dummy!", $coreSchema::SCHEMA_URI);
        $this->assertArrayHasKey('!dummy!', $this->tagFactory::$schemaHandles);
    }

    public function testTransform()
    {
        $scalarNode = new Scalar('somestring', 1);
        $transformed = $this->tagFactory::transform('!!str', $scalarNode);
        $this->assertEquals('somestring', $transformed);
    }

    public function testRunHandler()
    {
        $scalarNode = new Scalar('somestring', 1);
        $tagged = $this->tagFactory::runHandler('!!', 'str', $scalarNode);
        $this->assertEquals('somestring', $tagged);
    }

}
