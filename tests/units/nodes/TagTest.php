<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\Tag;

use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Tagged;

/**
 * Class TagTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\Tag
 */
class TagTest extends TestCase
{
    /**
     * @var Tag $nodeTag An instance of "Nodes\Tag" to test.
     */
    private $nodeTag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeTag = new Tag('!!str 654',0);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Tag::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new Blank('', 1);
        $this->assertFalse($this->nodeTag->isAwaitingChild($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Tag::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $uselessNode = new Blank('', 1);
        $parent = new Key(' key:', 1);
        $parent->add($this->nodeTag);
        $this->assertEquals($parent, $this->nodeTag->getTargetOnEqualIndent($uselessNode));
        // $this->nodeTag->value = null;
        // $this->assertEquals($this->nodeTag, $this->nodeTag->getTargetOnEqualIndent($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\Tag::build
     */
    public function testBuild(): void
    {
        // test value tranformed
        $parent = new Key(' key:',1);
        $parent->add($this->nodeTag);
        $built = $this->nodeTag->build();
        $this->assertEquals('654', $built);
        // test apply tag to YamlObject (cause value is null and parent is a NodeRoot)
        $rootNode = new Root();
        $rootNode->add($this->nodeTag);
        $this->nodeTag->value = null;
        $yamlObject = new YamlObject(0);
        $this->assertFalse($yamlObject->isTagged());
        // add yamlObject to NodeRoot
        $rootNode->build($yamlObject);// this triggers this->nodeTag->build
        $this->assertTrue($yamlObject->isTagged());
        // test "unknown" ag: must return a Tag object
        $this->nodeTag = new Tag('!!unknown 654',1);
        $built = $this->nodeTag->build($yamlObject);
        $this->assertTrue($built instanceof Tagged);
        $this->assertEquals("!!unknown", $built->tagName);
        $this->assertEquals("654", $built->value);
    }
}
