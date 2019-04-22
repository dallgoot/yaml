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
 * @link https://github.com/dallgoot/yaml
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
        $this->compact = new Compact([]);
    }

    /**
     * @covers \Dallgoot\Yaml\Compact::__construct
     */
    public function testConstruct(): void
    {
        $this->compact[12] = 'abc';
        $this->assertEquals('abc', $this->compact[12]);
        $this->compact->prop = '123';
        $this->assertEquals('123', $this->compact->prop);
    }

    /**
     * @covers \Dallgoot\Yaml\Compact::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $this->assertTrue(is_array($this->compact->jsonSerialize()));
    }
}
