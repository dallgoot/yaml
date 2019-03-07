<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Tag;

/**
 * Class TagTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Tag
 */
class TagTest extends TestCase
{
    /**
     * @var Tag $tag An instance of "Tag" to test.
     */
    private $tag;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->tag = new Tag("tagName", "a string to test");
    }

    /**
     * @covers \Dallgoot\Yaml\Tag::__construct
     */
    public function testConstruct(): void
    {
        $this->assertEquals("tagName",$this->tag->tagName);
        $this->assertEquals("a string to test",$this->tag->value);
    }
}
