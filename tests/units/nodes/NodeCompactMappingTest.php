<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Compact;
use Dallgoot\Yaml\NodeCompactMapping;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeCompactMappingTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeCompactMapping
 */
class NodeCompactMappingTest extends TestCase
{
    /**
     * @var NodeCompactMapping $nodeCompactMapping An instance of "NodeCompactMapping" to test.
     */
    private $nodeCompactMapping;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeCompactMapping = new NodeCompactMapping(" {a : 123, b: abc }  ", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactMapping::__construct
     */
    public function testConstruct(): void
    {
        $children = $this->nodeCompactMapping->value;
        $this->assertTrue($children instanceof NodeList);
        $this->assertTrue($children[0] instanceof NodeKey);
        $this->assertTrue($children[0]->value instanceof NodeScalar);
        $this->assertTrue($children[1] instanceof NodeKey);
        $this->assertTrue($children[1]->value instanceof NodeScalar);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactMapping::build
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
        $this->nodeCompactMapping = new NodeCompactMapping(" {  a : 123 }  ", 42);
        $result = $this->nodeCompactMapping->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertTrue(property_exists($result, 'a'));
        $this->assertEquals(123, $result->a);
    }
}
