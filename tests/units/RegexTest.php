<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Regex;

/**
 * Class RegexTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Regex
 */
class RegexTest extends TestCase
{
    /**
     * @var Regex $regex An instance of "Regex" to test.
     */
    private $regex;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->regex = new Regex();
    }

    /**
     * @covers \Dallgoot\Yaml\Regex::isDate
     */
    public function testIsDate(): void
    {
        $this->assertTrue($this->regex::isDate('2002-12-14'));
        $this->assertTrue($this->regex::isDate('2002/12/14'));
        $this->assertTrue($this->regex::isDate('2001-12-15T02:59:43.1Z'));
        $this->assertTrue($this->regex::isDate('2001-12-14 21:59:43.10 -5'));
        $this->assertTrue($this->regex::isDate('2001-12-14t21:59:43.10-05:00'));
        //not conforing dates
        $this->assertFalse($this->regex::isDate('20-12-2004'));
    }

    /**
     * @covers \Dallgoot\Yaml\Regex::isDate
     */
    public function testIsDateWithNoString(): void
    {
        $this->expectException(\TypeError::class);
        $this->assertFalse($this->regex::isDate(null));
    }

    /**
     * @covers \Dallgoot\Yaml\Regex::isNumber
     */
    public function testIsNumber(): void
    {
        $this->assertTrue($this->regex::isNumber('0o45'));
        $this->assertTrue($this->regex::isNumber('0xa7'));
        $this->assertTrue($this->regex::isNumber('123'));
        $this->assertTrue($this->regex::isNumber('123.456'));
        $this->assertTrue($this->regex::isNumber('.6'));
        // not standard numbers
        $this->assertFalse($this->regex::isNumber('123.45.6'));
        $this->assertFalse($this->regex::isNumber('0x'));
        $this->assertFalse($this->regex::isNumber('0o'));
    }

    /**
     * @covers \Dallgoot\Yaml\Regex::isProperlyQuoted
     */
    public function testIsProperlyQuoted(): void
    {
        $this->assertTrue($this->regex::isProperlyQuoted(' "  "  '));
        $this->assertTrue($this->regex::isProperlyQuoted(' " \" "  '));
        $this->assertTrue($this->regex::isProperlyQuoted(" '  '  "));
        $this->assertTrue($this->regex::isProperlyQuoted(" ' \' '  "));
        $this->assertTrue($this->regex::isProperlyQuoted("' 'a' 'b'  '"));
        $this->assertTrue($this->regex::isProperlyQuoted('" "a" "b"  "'));
        $this->assertTrue($this->regex::isProperlyQuoted('" \"a\" \'b\'  "'));

        $this->assertFalse($this->regex::isProperlyQuoted('" \"a\" \'b\'  '));

    }
}
