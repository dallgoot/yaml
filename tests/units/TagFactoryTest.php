<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\Node;
use Closure;

/**
 * Class TagFactoryTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\TagFactory
 */
class TagFactoryTest extends TestCase
{
    /**
     * @var TagFactory $tagFactory An instance of "TagFactory" to test.
     */
    private $tagFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->tagFactory = new TagFactory();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::registerLegacyTags
     */
    public function testRegisterLegacyTags(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::symfonyPHPobjectHandler
     */
    public function testSymfonyPHPobjectHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::inlineHandler
     */
    public function testInlineHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::strHandler
     */
    public function testStrHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::binaryHandler
     */
    public function testBinaryHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::setHandler
     */
    public function testSetHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::omapHandler
     */
    public function testOmapHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::transform
     */
    public function testTransform(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::isKnown
     */
    public function testIsKnown(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }

    /**
     * @covers \Dallgoot\Yaml\TagFactory::addTagHandler
     */
    public function testAddTagHandler(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
    }
}
