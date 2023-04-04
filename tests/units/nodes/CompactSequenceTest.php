<?php

namespace Test\Dallgoot\Yaml\Nodes;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Types\Compact;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\CompactSequence;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\JSON;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 * Class CompactSequenceTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Nodes\CompactSequence
 */
class CompactSequenceTest extends TestCase
{
    /**
     * @var CompactSequence $nodeCompactSequence An instance of "Nodes\CompactSequence" to test.
     */
    private $nodeCompactSequence;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeCompactSequence = new CompactSequence(" [ 1, ad, [456] ]", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\CompactSequence::__construct
     */
    public function testConstruct(): void
    {
        $children = $this->nodeCompactSequence->value;
        $this->assertTrue($children instanceof NodeList);
        $this->assertTrue($children[0] instanceof Item);
        $this->assertTrue($children[0]->value instanceof Scalar);
        $this->assertTrue($children[1] instanceof Item);
        $this->assertTrue($children[1]->value instanceof Scalar);
        $this->assertTrue($children[2] instanceof item);
        $this->assertTrue($children[2]->value instanceof JSON);
    }

    /**
     * @covers \Dallgoot\Yaml\Nodes\CompactSequence::build
     */
    public function testBuild(): void
    {
        $result = $this->nodeCompactSequence->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals(1, $result[0]);
        $this->assertArrayHasKey(1, $result);
        $this->assertEquals('ad', $result[1]);
        $this->assertArrayHasKey(2, $result);
        $this->assertEquals([456], $result[2]);
        $this->nodeCompactSequence->value = null;
        $this->assertTrue(is_null($this->nodeCompactSequence->build()));
        //test single NodeGeneric absorbed in NodeList
        $this->nodeCompactSequence = new CompactSequence(" [ [456] ]", 42);
        $result = $this->nodeCompactSequence->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals([456], $result[0]);
    }
}
