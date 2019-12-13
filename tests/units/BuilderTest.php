<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Builder;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\DocStart;
use Dallgoot\Yaml\Nodes\DocEnd;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Nodes\SetKey;

/**
 * Class BuilderTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Builder
 */
class BuilderTest extends TestCase
{
    /**
     * @var Builder $builder An instance of "Builder" to test.
     */
    private $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->builder = new Builder(0,0);
    }

    private function buildSimpleMapping()
    {
        // create a yaml mapping
        $root = new Root;
        $root->add(new Key('key: value', 1));
        return $this->builder->buildContent($root);
    }

    private function buildSimpleSequence()
    {
        // create a yaml sequence
        $root = new Root;
        $root->add(new Item('- itemvalue', 1));
        return $this->builder->buildContent($root);
    }

    private function buildMultiDoc()
    {
        $root = new Root;
        $root->add(new DocStart('---', 1));
        $root->add(new Key('key: value', 2));
        $root->add(new DocEnd('...', 3));
        $root->add(new DocStart('---', 4));
        $root->add(new Key('key: value', 5));
        $root->add(new DocStart('---', 6));
        $root->add(new Key('key: value', 7));
        return $this->builder->buildContent($root);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
     * @todo   test :
     *  simple literal
     *  only JSON content
     */
    public function testBuildContent(): void
    {
        $debug_property = new \ReflectionProperty($this->builder, '_debug');
        $debug_property->setAccessible(true);
        $debug_property->setValue($this->builder, 2);
        ob_start();
        $this->assertEquals($this->builder->buildContent(new Root), null);
        ob_end_clean();
    }
     /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
    */
    public function testBuildContentMAPPING(): void
    {
        //test simple mapping
        $yamlMapping = $this->buildSimpleMapping();
        $this->assertTrue($yamlMapping instanceof YamlObject);
        $this->assertTrue(property_exists($yamlMapping, 'key'));
        $this->assertEquals($yamlMapping->key, 'value');
     }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
    */
    public function testBuildContentSEQUENCE(): void
    {   //test simple sequence
        $yamlSequence = $this->buildSimpleSequence();
        $this->assertTrue($yamlSequence instanceof YamlObject);
        $this->assertArrayHasKey(0, $yamlSequence);
        $this->assertEquals($yamlSequence[0], 'itemvalue');
    }

     /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
    */
    public function testBuildContentMULTIDOC(): void
    {
        // test multi document
        $multiDoc = $this->buildMultiDoc();
        $this->assertTrue(is_array($multiDoc));
        $this->assertTrue(count($multiDoc) === 3);
        $this->assertArrayHasKey(0, $multiDoc);
        $this->assertTrue($multiDoc[0] instanceof YamlObject);
        $this->assertArrayHasKey(1, $multiDoc);
        $this->assertTrue($multiDoc[1] instanceof YamlObject);
        $this->assertArrayHasKey(2, $multiDoc);
        $this->assertTrue($multiDoc[2] instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
     * @todo   test :
     *  simple literal
     *  only JSON content
     *  multidocument
     */
    public function testBuildContentException(): void
    {
        $this->expectException(\Exception::class);
        $root = new Root;
        $root->value = null;
        $this->builder->buildContent($root);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildDocument
     */
    public function testBuildDocument(): void
    {
        $nodekey = new Key('key: keyvalue', 1);
        $list = new NodeList($nodekey);
        $yamlObject = $this->builder->buildDocument($list, 0);
        $this->assertTrue($yamlObject instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildDocument
     */
    public function testBuildDocumentDebug(): void
    {
        $output =
                "Document #0\n".
                "Dallgoot\Yaml\Nodes\Root Object\n".
                "(\n".
                "    [line->indent] =>  -> -1\n".
                "    [value] => Dallgoot\Yaml\NodeList Object\n".
                "        (\n".
                "            [type] => \n".
                "            [flags:SplDoublyLinkedList:private] => 0\n".
                "            [dllist:SplDoublyLinkedList:private] => Array\n".
                "                (\n".
                "                )\n".
                "\n".
                "        )\n".
                "\n".
                "    [raw] => \n".
                "    [parent] => NO PARENT!!!\n".
                ")\n";
        $debug = new \ReflectionProperty(Builder::class, '_debug');
        $debug->setAccessible(true);
        $debug->setValue($this->builder,3);
        $list = new NodeList;
        $this->builder->buildDocument($list, 0);
        $this->expectOutputString($output);
        $debug->setValue($this->builder,0);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildDocument
     */
    public function testBuildDocumentException(): void
    {
        $this->expectException(\Error::class);
        $list = new NodeList();
        $list->push(new \StdClass);
        $yamlObject = $this->builder->buildDocument($list, 0);
    }


    /**
     * @covers \Dallgoot\Yaml\Builder::pushAndSave
     */
    public function testPushAndSave(): void
    {
        $reflector = new \ReflectionClass($this->builder);
        $method = $reflector->getMethod('pushAndSave');
        $method->setAccessible(true);
        $child = new DocEnd('', 1);
        $buffer = new NodeList;
        $documents = [];
        $this->assertTrue($buffer->count() === 0);
        $method->invokeArgs($this->builder, [$child, &$buffer, &$documents]);
        $this->assertTrue($buffer->count() === 0);
        $this->assertTrue(count($documents) === 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::saveAndPush
     */
    public function testSaveAndPush(): void
    {
        $reflector = new \ReflectionClass($this->builder);
        $method = $reflector->getMethod('saveAndPush');
        $method->setAccessible(true);
        $itemNode = new Item('- item', 1);
        $buffer = new NodeList($itemNode);
        $child = new DocStart('', 1);
        $documents = [];
        $this->assertTrue($buffer->count() === 1);
        $method->invokeArgs($this->builder, [$child, &$buffer, &$documents]);
        $this->assertTrue($buffer->count() === 1);
        $this->assertTrue(count($documents) === 1);
        $this->assertTrue($buffer->offsetGet(0) instanceof DocStart);
    }
}
