<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Anchor;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Comment;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Literal;
use Dallgoot\Yaml\Nodes\Generic\Literals;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Scalar;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Types\YamlObject;

/**
 * Class KeyTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Key
 */
class KeyTest extends TestCase
{
    /**
     * @var Key $nodeKey An instance of "Nodes\Key" to test.
     */
    private $nodeKey;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeKey = new Key("key: value", 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeKey->value instanceof Scalar);
        $this->nodeKey = new Key("key:", 1);
        $this->assertTrue(is_null($this->nodeKey->value));
        $this->nodeKey = new Key("key: ", 1);
        $this->assertTrue(is_null($this->nodeKey->value));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::__construct
     */
    public function testConstructException(): void
    {
        $this->expectException(\ParseError::class);
        $this->nodeKey->__construct('not a key at all and no matches', 1);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::setIdentifier
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
     * @covers \Dallgoot\Yaml\Nodes\Key::setIdentifier
     */
    public function testSetIdentifierAsEmptyString()
    {
        $this->expectException(\ParseError::class);
        $this->nodeKey->setIdentifier('');
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::add
     */
    public function testAdd(): void
    {
        $scalarNode = new Scalar('sometext', 2);
        $this->nodeKey = new Key("key:", 1);
        $this->nodeKey->add($scalarNode);
        $this->assertEquals($scalarNode, $this->nodeKey->value);
        //
        $this->nodeKey = new Key("key: |", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLit = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLit->value->offsetGet(0));
        //
        $this->nodeKey = new Key("key: >", 1);
        $this->nodeKey->add($scalarNode);
        $nodeLitFolded = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeLitFolded->value->offsetGet(0));
        //
        $this->nodeKey = new Key("key: &anchor", 1);
        $this->nodeKey->add($scalarNode);
        $nodeAnchor = $this->nodeKey->value;
        $this->assertEquals($scalarNode, $nodeAnchor->value);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $nodeItem = new Item('  - an_item', 2);
        $this->nodeKey = new Key("  key:", 1);
        $this->assertEquals($this->nodeKey, $this->nodeKey->getTargetOnEqualIndent($nodeItem));

        $blankNode = new Blank('', 3);
        $rootNode = new Root;
        $rootNode->add($this->nodeKey);
        $this->assertEquals($this->nodeKey->getParent(), $this->nodeKey->getTargetOnEqualIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::getTargetOnMoreIndent
     */
    public function testGetTargetOnMoreIndent(): void
    {
        $blankNode = new Blank('', 2);
        $this->assertEquals($this->nodeKey, $this->nodeKey->getTargetOnMoreIndent($blankNode));

        $this->nodeKey = new Key("  key: &anchor |", 1);
        $anchorNode = $this->nodeKey->value;
        $this->assertEquals($anchorNode->value, $this->nodeKey->getTargetOnMoreIndent($blankNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $blankNode = new Blank('', 42);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new Comment('# this is a comment', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new Scalar('this is a text', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild($blankNode));
        //
        $this->nodeKey->value = new Item(' - item', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new Item(' - item2', 2)));
        //
        $this->nodeKey->value = new Key(' key1:', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new Key(' key2:', 2)));
        //
        $this->nodeKey->value = new Literal(' |', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new Key('  key2:', 2)));
        //
        $this->nodeKey->value = new Anchor(' &anchor', 1);
        $this->assertTrue($this->nodeKey->isAwaitingChild(new Key('  key2:', 2)));
        //
        $this->nodeKey->value = new Anchor(' &anchor already have a value', 1);
        $this->assertFalse($this->nodeKey->isAwaitingChild(new Key('  key2:', 2)));
    }
    /**
     * @covers \Dallgoot\Yaml\Nodes\Key::build
     */
    public function testBuild(): void
    {
        $keyTagged = new Key('!!str 1.2: value', 1);
        $built = $keyTagged->build();
        $this->assertTrue(property_exists($built, '1.2'));
        $this->assertEquals('value', $built->{'1.2'});
        //
        $built = $this->nodeKey->build();
        $this->assertTrue(property_exists($built, 'key'));
        $this->assertEquals('value', $built->key);
        //
        $parent = new \stdClass;
        $built = $this->nodeKey->build($parent);
        $this->assertTrue(property_exists($parent, 'key'));
        $this->assertEquals('value', $parent->key);
        //
        $parent = new YamlObject(0);
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
