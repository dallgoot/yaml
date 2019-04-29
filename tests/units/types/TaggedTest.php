<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Tagged;

/**
 * Class TaggedTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Tagged
 */
class TaggedTest extends TestCase
{
    /**
     * @var Tagged $tag An instance of "Tagged" to test.
     */
    private $tag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->tag = new Tagged("tagName", "a string to test");
    }

    /**
     * @covers \Dallgoot\Yaml\Tagged::__construct
     */
    public function testConstruct(): void
    {
        $this->assertEquals("tagName",$this->tag->tagName);
        $this->assertEquals("a string to test",$this->tag->value);
    }

    /**
     * @covers \Dallgoot\Yaml\Tagged::__construct
     */
    public function testConstructEmptyName(): void
    {
        $this->expectException(\Exception::class);
        $this->tag = new Tagged("", "a string to test");
    }
}
