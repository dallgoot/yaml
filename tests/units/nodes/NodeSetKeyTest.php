<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeSetKey;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeSetKeyTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeSetKey
 */
class NodeSetKeyTest extends TestCase
{
    /**
     * @var NodeSetKey $nodeSetKey An instance of "NodeSetKey" to test.
     */
    private $nodeSetKey;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeSetKey = new NodeSetKey("   ?  someStringKey", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeSetKey::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeSetKey->value instanceof NodeScalar);
        $this->assertEquals('someStringKey', $this->nodeSetKey->value->build());
    }

    /**
     * @covers \Dallgoot\Yaml\NodeSetKey::build
     */
    public function testBuild(): void
    {
        $parent = new \StdClass;
        $built = $this->nodeSetKey->build($parent);
        $this->assertTrue(property_exists($parent, 'someStringKey'));
        $this->assertEquals(null, $parent->someStringKey);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeSetKey::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new NodeBlank('', 1);
        $this->assertFalse($this->nodeSetKey->isAwaitingChild($uselessNode));
        $this->nodeSetKey->value = null;
        $this->assertTrue($this->nodeSetKey->isAwaitingChild($uselessNode));
    }
}
