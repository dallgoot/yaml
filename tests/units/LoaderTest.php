<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Loader;
use Generator;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class LoaderTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Loader
 */
class LoaderTest extends TestCase
{
    /**
     * @var Loader $loader An instance of "Loader" to test.
     */
    private $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->loader = new Loader();
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(\Exception::class);
        $this->loader->__construct("non sense string");
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::load
     */
    public function testLoad(): void
    {
        $this->expectException(\Exception::class);
        $this->loader->load('http://www.example.com/');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::getSourceGenerator
     */
    public function testGetSourceGenerator(): void
    {
        // $this->expectException(\Exception::class);
        $reflector = new \ReflectionClass($this->loader);
        $method = $reflector->getMethod('getSourceGenerator');
        $method->setAccessible(true);
        $result = $method->invoke($this->loader);
        $this->assertTrue($result instanceof \Generator, 'getSourceGenerator is NOT a \\Generator');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::parse
     */
    public function testParse(): void
    {
        $result = $this->loader->parse('key: keyvalue');
        $this->assertTrue($result instanceof YamlObject);
        $multidoc = $this->loader->parse("---\nkey1: key1value\n---\nkey1: key1value\n");
        $this->assertTrue(is_array($multidoc), 'result is NOT a multi-documents (ie. array)');
        $this->assertTrue($multidoc[0] instanceof YamlObject, 'array #0 is NOT a YamlObject');
        $this->assertTrue($multidoc[1] instanceof YamlObject, 'array #1 is NOT a YamlObject');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::attachBlankLines
     */
    public function testAttachBlankLines(): void
    {
        $rootNode = new NodeRoot();
        $blankNode = new NodeBlank('', 1);
        // NodeBlank get private method
        $reflectorBlank = new \ReflectionClass($blankNode);
        $method = $reflectorBlank->getMethod('setParent');
        $method->setAccessible(true);
        // blankNode->setParent($rootNode) : sets the parent for blankNode
        $method->invoke($blankNode, $rootNode);
        $this->assertTrue($rootNode->value->count() === 0, 'rootNode has a child yet');
        // Loader get private property '_blankBuffer'
        $reflectorLoader = new \ReflectionClass($this->loader);
        $blankBufferProp = $reflectorLoader->getProperty('_blankBuffer');
        $blankBufferProp->setAccessible(true);
        $this->assertTrue(count($blankBufferProp->getValue($this->loader)) === 0, '_blankbuffer is NOT empty');
        // set _blankbuffer : ie. add blankNode
        $blankBufferProp->setValue($this->loader, [$blankNode]);
        $this->assertTrue(count($blankBufferProp->getValue($this->loader)) === 1, 'blankBuffer has NO content');
        $this->assertTrue($blankBufferProp->getValue($this->loader)[0] instanceof NodeBlank, 'blankBuffer has NO nodeBlank');
        //attach to parents => add child to parent
        $this->loader->attachBlankLines($rootNode);
        $this->assertTrue(count($blankBufferProp->getValue($this->loader)) === 0, '_blankbuffer is NOT empty');
        $this->assertTrue($rootNode->value->count() === 1, 'rootnode has NO child');
        $this->assertTrue($rootNode->value->offsetGet(0) instanceof NodeBlank, 'rootnode child is NOT a blanknode');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::needsSpecialProcess
     * @todo assert a true value also
     */
    public function testNeedsSpecialProcess(): void
    {
        $current  = new NodeScalar('some text', 1);
        $previous = new NodeRoot();
        $this->assertFalse($this->loader->needsSpecialProcess($current, $previous));
        $current  = new NodeBlank('', 1);
        $previous = new NodeRoot();
        $this->assertTrue($this->loader->needsSpecialProcess($current, $previous));
        $previous = new NodeKey('key: "partial value', 1);
        $current  = new NodeScalar(' end of partial value"',2);
        $this->assertTrue($this->loader->needsSpecialProcess($current, $previous));
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::onError
     */
    public function testOnError(): void
    {
        $this->expectException(\Exception::class);
        $generator = function() {
            yield 1 => 'this is the first line';
            yield 2 => 'this is the second line';
        };
        $this->loader->onError(new \Exception, $generator());
    }
}
