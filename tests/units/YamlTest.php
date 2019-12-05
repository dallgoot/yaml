<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Nodes\Root;

/**
 * Class YamlTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml
 */
class YamlTest extends TestCase
{
    /**
     * @var Yaml $yaml An instance of "Yaml" to test.
     */
    private $yaml;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->yaml = new Yaml();
    }

    /**
     * @covers \Dallgoot\Yaml::parse
     */
    public function testParse(): void
    {
        $yaml = "- 1\n- 2\n- 3\n";
        $this->assertTrue($this->yaml::parse($yaml) instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml::parse
     */
    public function testParseException(): void
    {
        $this->expectException(\Exception::class);
        $this->yaml::parse('::');
    }

    /**
     * @covers \Dallgoot\Yaml::parseFile
     */
    public function testParseFile(): void
    {
        $this->assertTrue($this->yaml::parseFile(__DIR__."/../definitions/parsing_tests.yml") instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml::parseFile
     */
    public function testParseFileException(): void
    {
        $this->expectException(\Exception::class);
        $this->yaml::parseFile('ssh:example.com');
    }

    /**
     * @covers \Dallgoot\Yaml::dump
     */
    public function testDump(): void
    {
        $this->assertEquals("- 1\n- 2\n- 3", $this->yaml::dump([1,2,3]));
        $this->assertEquals("--- some text\n", $this->yaml::dump('some text'));
    }

    /**
     * @covers \Dallgoot\Yaml::dump
     */
    public function testDumpException(): void
    {
        $this->expectException(\Throwable::class);
        $this->yaml::dump(null);
    }

    /**
     * @covers \Dallgoot\Yaml::dumpFile
     */
    public function testDumpFile(): void
    {
        $filename = 'dumperTest.yml';
        $result = $this->yaml::dumpFile($filename, [1,2,3]);
        $this->assertTrue($result);
        $this->assertEquals("- 1\n- 2\n- 3", file_get_contents($filename));
        unlink($filename);
    }

    /**
     * @covers \Dallgoot\Yaml::dumpFile
     */
    public function testDumpFileException(): void
    {
        $this->expectException(\Throwable::class);
        $this->yaml::dumpFile('someFileName', null);
    }
}
