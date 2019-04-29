<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Scalar;
use Dallgoot\Yaml\Nodes\SetKey;

/**
 * Class SetKeyTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\SetKey
 */
class SetKeyTest extends TestCase
{
    /**
     * @var SetKey $nodeSetKey An instance of "Nodes\SetKey" to test.
     */
    private $nodeSetKey;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->nodeSetKey = new SetKey("   ?  someStringKey", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\SetKey::__construct
     */
    public function testConstruct(): void
    {
        $this->assertTrue($this->nodeSetKey->value instanceof Scalar);
        $this->assertEquals('someStringKey', $this->nodeSetKey->value->build());
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\SetKey::build
     */
    public function testBuild(): void
    {
        $parent = new \StdClass;
        $built = $this->nodeSetKey->build($parent);
        $this->assertTrue(property_exists($parent, 'someStringKey'));
        $this->assertEquals(null, $parent->someStringKey);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\SetKey::isAwaitingChild
     */
    public function testIsAwaitingChild(): void
    {
        $uselessNode = new Blank('', 1);
        $this->assertFalse($this->nodeSetKey->isAwaitingChild($uselessNode));
        $this->nodeSetKey->value = null;
        $this->assertTrue($this->nodeSetKey->isAwaitingChild($uselessNode));
    }
}
