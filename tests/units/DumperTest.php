<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Dumper;
use Dallgoot\Yaml\YamlObject;

/**
 * Class DumperTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Dumper
 */
class DumperTest extends TestCase
{
    /**
     * @var Dumper $dumper An instance of "Dumper" to test.
     */
    private $dumper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->dumper = new Dumper();
    }

    /**
     * @covers \Dallgoot\Yaml\Dumper::toString
     */
    public function testToString(): void
    {
        $this->assertEquals("- 1\n- 2\n- 3", $this->dumper::toString([1,2,3]));
        $this->assertEquals("--- some text\n", $this->dumper::toString('some text'));
    }

    /**
     * @covers \Dallgoot\Yaml\Dumper::toFile
     */
    public function testToFile(): void
    {
        $filename = 'dumperTest.yml';
        $result = $this->dumper::toFile($filename, [1,2,3]);
        $this->assertTrue($result);
        $this->assertEquals("- 1\n- 2\n- 3", file_get_contents($filename));
        unlink($filename);
    }

}
