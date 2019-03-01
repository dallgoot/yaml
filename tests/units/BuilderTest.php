<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\Builder;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\YamlObject;
use Dallgoot\Yaml\Node;

/**
 * Class BuilderTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\Builder
 */
class BuilderTest extends TestCase
{
    /**
     * @var Builder $builder An instance of "Builder" to test.
     */
    private $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->builder = new Builder();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildContent
     */
    public function testBuildContent(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::buildDocument
     */
    public function testBuildDocument(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::getScalar
     */
    public function testGetScalar(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::getNumber
     */
    public function testGetNumber(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::pushAndSave
     */
    public function testPushAndSave(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\Builder::saveAndPush
     */
    public function testSaveAndPush(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
