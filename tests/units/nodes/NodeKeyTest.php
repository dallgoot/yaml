<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeComment;
use Dallgoot\Yaml\NodeLiterals;
use Dallgoot\Yaml\NodeAnchor;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeKeyTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeKey
 */
class NodeKeyTest extends TestCase
{
    /**
     * @var NodeKey $nodeKey An instance of "NodeKey" to test.
     */
    private $nodeKey;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeKey = new NodeKey("key: value", 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeKey->value instanceof NodeScalar);
        $this->nodeKey = new NodeKey("key:", 1);
        $this->assertTrue(is_null($this->nodeKey->value));
        $this->nodeKey = new NodeKey("key: ", 1);
        $this->assertTrue(is_null($this->nodeKey->value));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::__construct
     */
    public function testConstructException(): void
    {
        $this->expectException(\Exception::class);
        $this->nodeKey->__construct('not a key at all and no matches', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::setIdentifier
     */
    public function testSetIdentifier(): void
    {
        $reflector = new \ReflectionClass($this->nodeKey);
        $identifier = $reflector->getProperty('identifier');
        $identifier->setAccessible(true);

        $this->nodeKey->setIdentifier('newkey');
        $this->assertEquals('newkey', $identifier->getValue($this->nodeKey));

        $this->nodeKey->setIdentifier('!!str 1.2');
        $this->assertEquals(null, $this->nodeKey->tag);
        $this->assertEquals('1.2', $identifier->getValue($this->nodeKey));

        $this->nodeKey->setIdentifier('&anchor 1.2');
        $this->assertEquals(null, $this->nodeKey->anchor);
        $this->assertEquals('1.2', $identifier->getValue($this->nodeKey));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::setIdentifier
     */
    public function testSetIdentifierAsEmptyString()
    {
        $this->expectException(\ParseError::class);
        $this->nodeKey->setIdentifier('');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::add
     */
    public function testAdd(): void
    {
        $scalarNode = new NodeScalar('sometext', 2);
        $this->nodeKey = new NodeKey("key:", 1);
        $this->nodeKey->add($scalarNode);
        $this->assertEquals($scalarNode, $this->nodeKey->value);
        //
        $this->nodeKey = new NodeKey("key: |", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLit = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLit->value->offsetGet(0));
        //
        $this->nodeKey = new NodeKey("key: >", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLitFolded = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLitFolded->value->offsetGet(0));
        //
        $this->nodeKey = new NodeKey("key: &anchor", 1);
        $this->nodeKey->add($scalarNode);
        $nodeAnchor = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeAnchor->value);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $nodeItem = new NodeItem('  - an_item', 2);
        $this->nodeKey = new NodeKey("  key:", 1);
        $this->assertEquals($this->nodeKey, $this->nodeKey->getTargetOnEqualIndent($nodeItem));

        $blankNode = new NodeBlank('', 3);
        $rootNode = new NodeRoot;
        $rootNode->add($this->nodeKey);
        $this->assertEquals($this->nodeKey->getParent(), $this->nodeKey->getTargetOnEqualIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $blankNode = new NodeBlank('', 2);
        $this->assertEquals($this->nodeKey, $this->nodeKey->getTargetOnMoreIndent($blankNode));

        $this->nodeKey = new NodeKey("  key: &anchor |", 1);
        $anchorNode = $this->nodeKey->value;
        $this->assertEquals($anchorNode->value, $this->nodeKey->getTargetOnMoreIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeKey::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $blankNode = new NodeBlank('', 42);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new NodeComment('# this is a comment', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new NodeScalar('this is a text', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new NodeItem(' - item', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new NodeItem(' - item2', 2)));
        //
        $this->nodeKey->value = new NodeKey(' key1:', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new NodeKey(' key2:', 2)));
        //
        $this->nodeKey->value = new NodeLit(' |', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new NodeKey('  key2:', 2)));
        //
        $this->nodeKey->value = new NodeAnchor(' &anchor', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new NodeKey('  key2:', 2)));
        //
        $this->nodeKey->value = new NodeAnchor(' &anchor already have a value', 1);
        $this->assertFalse($this->nodeKey->isAwaitingChild(new NodeKey('  key2:', 2)));
    }
    /**
     * @covers \Dallgoot\Yaml\NodeKey::build
     */
    public function testBuild(): void
    {
        $keyTagged = new NodeKey('!!str 1.2: value', 1);
        $built = $keyTagged->build();
        $this->assertTrue(property_exists($built, '1.2'));
        $this->assertEquals('value', $built->{'1.2'});
        //
        $built = $this->nodeKey->build();
        $this->assertTrue(property_exists($built, 'key'));
        $this->assertEquals('value', $built->key);
        //
        $parent = new \StdClass;
        $built = $this->nodeKey->build($parent);
        $this->assertTrue(property_exists($parent, 'key'));
        $this->assertEquals('value', $parent->key);
        //
        $parent = new YamlObject;
        $built = $this->nodeKey->build($parent);
        $this->assertTrue(property_exists($parent, 'key'));
        $this->assertEquals('value', $parent->key);
        //
        $this->nodeKey->value = null;
        $built = $this->nodeKey->build();
        $this->assertTrue(property_exists($built, 'key'));
        $this->assertEquals(null, $built->key);
    }
}
