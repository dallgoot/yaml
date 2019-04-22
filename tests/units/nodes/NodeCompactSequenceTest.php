<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Compact;
use Dallgoot\Yaml\NodeCompactSequence;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\NodeJSON;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\NodeScalar;

/**
 * Class NodeCompactSequenceTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeCompactSequence
 */
class NodeCompactSequenceTest extends TestCase
{
    /**
     * @var NodeCompactSequence $nodeCompactSequence An instance of "NodeCompactSequence" to test.
     */
    private $nodeCompactSequence;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nodeCompactSequence = new NodeCompactSequence(" [ 1, ad, [456] ]", 42);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactSequence::__construct
     */
    public function testConstruct(): void
    {
        $children = $this->nodeCompactSequence->value;
        $this->assertTrue($children instanceof NodeList);
        $this->assertTrue($children[0] instanceof NodeItem);
        $this->assertTrue($children[0]->value instanceof NodeScalar);
        $this->assertTrue($children[1] instanceof NodeItem);
        $this->assertTrue($children[1]->value instanceof NodeScalar);
        $this->assertTrue($children[2] instanceof Nodeitem);
        $this->assertTrue($children[2]->value instanceof NodeJSON);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeCompactSequence::build
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
        //test single Node absorbed in NodeList
        $this->nodeCompactSequence = new NodeCompactSequence(" [ [456] ]", 42);
        $result = $this->nodeCompactSequence->build();
        $this->assertTrue($result instanceof Compact);
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals([456], $result[0]);
    }
}
