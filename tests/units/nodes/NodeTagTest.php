<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeTag;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Tag;

/**
 * Class NodeTagTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeTag
 */
class NodeTagTest extends TestCase
{
    /**
     * @var NodeTag $nodeTag An instance of "NodeTag" to test.
     */
    private $nodeTag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeTag = new NodeTag('!!str 654',1);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $this->assertFalse($this->nodeTag->isAwaitingChild($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::getTargetOnEqualIndent
     */
    public function testGetTargetOnEqualIndent(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $parent = new NodeKey(' key:', 1);
        $parent->add($this->nodeTag);
        $this->assertEquals($parent, $this->nodeTag->getTargetOnEqualIndent($uselessNode));
        $this->nodeTag->value = null;
        $this->assertEquals($this->nodeTag, $this->nodeTag->getTargetOnEqualIndent($uselessNode));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeTag::build
     */
    public function testBuild(): void
    {
        // test value tranformed
        $parent = new NodeKey(' key:',1);
        $parent->add($this->nodeTag);
        $built = $this->nodeTag->build();
        $this->assertEquals('654', $built);
        // test apply tag to YamlObject (cause value is null and parent is a NodeRoot)
        $rootNode = new NodeRoot();
        $rootNode->add($this->nodeTag);
        $this->nodeTag->value = null;
        $yamlObject = new YamlObject;
        $this->assertFalse($yamlObject->isTagged());
        // add yamlObject to NodeRoot
        $rootNode->build($yamlObject);// this triggers this->nodeTag->build
        $this->assertTrue($yamlObject->isTagged());
        // test "unknown" ag: must return a Tag object
        $this->nodeTag = new NodeTag('!!unknown 654',1);
        $built = $this->nodeTag->build();
        $this->assertTrue($built instanceof Tag);
        $this->assertEquals("!!unknown", $built->tagName);
        $this->assertEquals("654", $built->value);
    }
}
