<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Compact;

/**
 * Class CompactTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Compact
 */
class CompactTest extends TestCase
{
    /**
     * @var Compact $compact An instance of "Compact" to test.
     */
    private $compact;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe check arguments of this constructor. */
        $this->compact = new Compact("a string to test");
    }

    /**
     * @covers \Dallgoot\Yaml\Compact::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Compact::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
