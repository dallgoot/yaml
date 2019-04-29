<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Compact;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\CompactMapping;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class CompactMappingTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\CompactMapping
 */
class CompactMappingTest extends TestCase
{
    /**
     * @var CompactMapping $nodeCompactMapping An instance of "Nodes\CompactMapping" to test.
     */
    private $nodeCompactMapping;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeCompactMapping = new CompactMapping(" {a : 123, b: abc }  ", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\CompactMapping::__construct
     */
    public function testConstruct(): void
    {
        $children = $this->nodeCompactMapping->value;
        $this->assertTrue($children instanceof NodeList);
        $this->assertTrue($children[0] instanceof Key);
        $this->assertTrue($children[0]->value instanceof Scalar);
        $this->assertTrue($children[1] instanceof Key);
        $this->assertTrue($children[1]->value instanceof Scalar);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\CompactMapping::build
     */
    public function testBuild(): void
    {
        $result = $this->nodeCompactMapping->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertTrue(property_exists($result, 'a'));
        $this->assertEquals(123, $result->a);
        $this->assertTrue(property_exists($result, 'b'));
        $this->assertEquals('abc', $result->b);
        $this->nodeCompactMapping->value = null;
        $this->assertTrue(is_null($this->nodeCompactMapping->build()));
        // test single value converted to list
        $this->nodeCompactMapping = new CompactMapping(" {  a : 123 }  ", 42);
        $result = $this->nodeCompactMapping->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertTrue(property_exists($result, 'a'));
        $this->assertEquals(123, $result->a);
    }
}
