<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Generator;
use Dallgoot\Yaml\Loader;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Partial;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class LoaderTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
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
     * @covers \Dallgoot\Yaml\Loader::getSourceGenerator
     */
    public function testLoad(): void
    {
        $this->assertEquals($this->loader, $this->loader->load(__DIR__.'/../definitions/parsing_tests.yml'));
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::load
     */
    public function testLoadNoFile(): void
    {
        $this->expectException(\Exception::class);
        $this->loader->load('./non_existent_file');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::load
     * @todo : make sure this tests covers the last Exception in method
     */
    // public function testLoadNoRights(): void
    // {
    //     if (strpos('microsoft', php_uname())) {
    //         $this->markTestSkipped(
    //           "this test won't work on WSL Windows"
    //         );
    //     }
    //     $this->expectException(\Exception::class);
    //     $fileName = "./notreadable";
    //     touch($fileName);
    //     chmod($fileName, 0222);
    //     $this->loader->load($fileName);
    //     unlink($fileName);
    // }

    /**
     * @covers \Dallgoot\Yaml\Loader::getSourceGenerator
     */
    public function testGetSourceGenerator(): void
    {
        $method = new \ReflectionMethod($this->loader, 'getSourceGenerator');
        $method->setAccessible(true);
        $result = $method->invoke($this->loader, '');
        $this->assertTrue($result instanceof \Generator, 'getSourceGenerator is NOT a \\Generator');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::getSourceGenerator
     */
    public function testGetSourceGeneratorException(): void
    {
        $this->expectException(\Exception::class);
        $method = new \ReflectionMethod($this->loader, 'getSourceGenerator');
        $method->setAccessible(true);
        $generator = $method->invoke($this->loader, null);
        $generator->next();
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::getSourceGenerator
     */
    public function testGetSourceGeneratorExceptionOnNoSource(): void
    {
        $this->expectException(\Exception::class);
        $method = new \ReflectionMethod($this->loader, 'getSourceGenerator');
        $method->setAccessible(true);
        $property = new \ReflectionProperty($this->loader, 'content');
        $property->setAccessible(true);
        $property->setValue($this->loader, []);
        $generator = $method->invoke($this->loader, null);
        $generator->next();
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::parse
     */
    public function testParse(): void
    {
        $result = $this->loader->parse("key: keyvalue\n  other string\notherkey: othervalue");
        $this->assertTrue($result instanceof YamlObject);
        $result = $this->loader->parse('key: keyvalue');
        $this->assertTrue($result instanceof YamlObject);
        $multidoc = $this->loader->parse("---\nkey1: key1value\n---\nkey1: key1value\n");
        $this->assertTrue(is_array($multidoc), 'result is NOT a multi-documents (ie. array)');
        $this->assertTrue($multidoc[0] instanceof YamlObject, 'array #0 is NOT a YamlObject');
        $this->assertTrue($multidoc[1] instanceof YamlObject, 'array #1 is NOT a YamlObject');
        $yamlMapping = $this->loader->parse("key:\n    insidekey: value\nlessindent: value");
        $this->assertTrue($yamlMapping instanceof YamlObject);
        $this->assertTrue(\property_exists($yamlMapping, 'key'));
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::parse
     */
    public function testParseWithError(): void
    {
        $this->expectException(\Exception::class);
        // fails because theres no NodeSetKey before
        $result = $this->loader->parse(' :: keyvalue');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::_attachBlankLines
     */
    public function testAttachBlankLines(): void
    {
        $rootNode = new Root();
        $blankNode = new Blank('', 1);
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
        $this->assertTrue($blankBufferProp->getValue($this->loader)[0] instanceof Blank, 'blankBuffer has NO nodeBlank');
        //attach to parents => add child to parent
        // $this->loader->attachBlankLines($rootNode);
        $attachBlankLinesMethod = new \ReflectionMethod($this->loader, '_attachBlankLines');
        $attachBlankLinesMethod->setAccessible(true);
        $attachBlankLinesMethod->invoke($this->loader, $rootNode);
        $this->assertTrue(count($blankBufferProp->getValue($this->loader)) === 0, '_blankbuffer is NOT empty');
        $this->assertTrue($rootNode->value->count() === 1, 'rootnode has NO child');
        $this->assertTrue($rootNode->value->offsetGet(0) instanceof Blank, 'rootnode child is NOT a blanknode');
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::needsSpecialProcess
     * @todo assert a true value also
     */
    public function testNeedsSpecialProcess(): void
    {
        $needsSpecialProcessMethod = new \ReflectionMethod($this->loader, 'needsSpecialProcess');
        $needsSpecialProcessMethod->setAccessible(true);
        $current  = new Scalar('some text', 1);
        $previous = new Root();
        // $this->assertFalse($this->loader->needsSpecialProcess($current, $previous));
        $this->assertFalse($needsSpecialProcessMethod->invoke($this->loader, $current, $previous));
        $current  = new Blank('', 1);
        $previous = new Root();
        // $this->assertTrue($this->loader->needsSpecialProcess($current, $previous));
        $this->assertTrue($needsSpecialProcessMethod->invoke($this->loader, $current, $previous));
        $previous = new Key('key: "partial value', 1);
        $current  = new Scalar(' end of partial value"',2);
        // $this->assertTrue($this->loader->needsSpecialProcess($current, $previous));
        $this->assertTrue($needsSpecialProcessMethod->invoke($this->loader, $current, $previous));
        $current = new Partial(' " oddly quoted');
        // $this->assertFalse($this->loader->needsSpecialProcess($current, $previous));
        $this->assertFalse($needsSpecialProcessMethod->invoke($this->loader, $current, $previous));
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
        // $this->loader->onError(new \Exception, $generator());
        $onErrorMethod = new \ReflectionMethod($this->loader, 'onError');
        $onErrorMethod->setAccessible(true);
        $onErrorMethod->invoke(new \Exception, $generator());
    }

    /**
     * @covers \Dallgoot\Yaml\Loader::onError
     */
    public function testOnErrorButNoException(): void
    {
        $this->loader = new Loader(null, Loader::NO_PARSING_EXCEPTIONS);
        $generator = function() {
            yield 1 => 'this is the first line';
            yield 2 => 'this is the second line';
        };
        $onErrorMethod = new \ReflectionMethod($this->loader, 'onError');
        $onErrorMethod->setAccessible(true);
        $this->assertEquals(null, $onErrorMethod->invoke($this->loader, new \Exception, $generator()->key()));
    }
}
