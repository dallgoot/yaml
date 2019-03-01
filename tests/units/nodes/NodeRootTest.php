<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeRootTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeRoot
 */
class NodeRootTest extends TestCase
{
    /**
     * @var NodeRoot $nodeRoot An instance of "NodeRoot" to test.
     */
    private $nodeRoot;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeRoot = new NodeRoot();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getParent
     */
    public function testGetParent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getRoot
     */
    public function testGetRoot(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::getYamlObject
     */
    public function testGetYamlObject(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::build
     */
    public function testBuild(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeRoot::buildFinal
     */
    public function testBuildFinal(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
