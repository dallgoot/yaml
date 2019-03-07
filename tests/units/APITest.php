<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use Dallgoot\Yaml\API;
use Dallgoot\Yaml\YamlObject;

/**
 * Class APITest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/john-doe/my-awesome-project
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\API
 */
class APITest extends TestCase
{
    /**
     * @var API $api An instance of "API" to test.
     */
    private $api;

    private $refValue = 123;
    private $commentValue = '# this a full line comment';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->api = new API(new YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\API::__construct
     */
    public function testConstruct(): void
    {
        /** @todo Complete this unit test method. */
        $this->markTestIncomplete();
        //should test that property '_obj' is null BEFORE construct ???
    }

    /**
     * @covers \Dallgoot\Yaml\API::addReference
     */
    public function testAddReference(): void
    {
        $this->assertEquals($this->api->getAllReferences(), []);
        $this->api->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->api->getReference('referenceName'), $this->refValue);
    }

    /**
     * @covers \Dallgoot\Yaml\API::getReference
     */
    public function testGetReference(): void
    {
        $this->assertEquals($this->api->getAllReferences(), []);
        $this->api->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->api->getReference('referenceName'), $this->refValue);
    }

    /**
     * @covers \Dallgoot\Yaml\API::getAllReferences
     */
    public function testGetAllReferences(): void
    {
        $this->assertEquals($this->api->getAllReferences(), []);
        $this->api->addReference('referenceName', $this->refValue);
        $this->assertEquals($this->api->getAllReferences(), ['referenceName' => $this->refValue]);
    }

    /**
     * @covers \Dallgoot\Yaml\API::addComment
     */
    public function testAddComment(): void
    {
        $this->assertEquals($this->api->getComment(), []);
        $this->api->addComment(20, $this->commentValue);
        $this->assertEquals($this->api->getComment(20), $this->commentValue);
    }

    /**
     * @covers \Dallgoot\Yaml\API::getComment
     * @depends testAddComment
     */
    public function testGetComment(): void
    {
        $this->assertEquals($this->api->getComment(), []);
        $this->api->addComment(20, $this->commentValue);
        $this->assertEquals($this->api->getComment(20), $this->commentValue);
    }

    /**
     * @covers \Dallgoot\Yaml\API::setText
     */
    public function testSetText(): void
    {
        $this->assertTrue(is_null($this->api->value));
        $txt = '      a  text with leading spaces';
        $yamlObject = $this->api->setText($txt);
        $this->assertTrue($this->api->value === ltrim($txt));
        $this->assertTrue($yamlObject instanceof YamlObject);
    }

    /**
     * @covers \Dallgoot\Yaml\API::addTag
     */
    public function testAddTag(): void
    {
        $this->assertFalse($this->api->isTagged());
        $this->api->addTag('!tagName');
        $this->assertTrue($this->api->isTagged());
    }

    /**
     * @covers \Dallgoot\Yaml\API::hasDocStart
     */
    public function testHasDocStart(): void
    {
        $this->assertFalse($this->api->hasDocStart());
        $this->api->setDocStart(false);
        $this->assertTrue($this->api->hasDocStart());
        $this->api->setDocStart(true);
        $this->assertTrue($this->api->hasDocStart());
        $this->api->setDocStart(null);
        $this->assertFalse($this->api->hasDocStart());
    }

    /**
     * @covers \Dallgoot\Yaml\API::setDocStart
     */
    public function testSetDocStart(): void
    {
        $this->assertFalse($this->api->hasDocStart());
        $this->api->setDocStart(false);
        $this->assertTrue($this->api->hasDocStart());
        $this->api->setDocStart(true);
        $this->assertTrue($this->api->hasDocStart());
        $this->api->setDocStart(null);
        $this->assertFalse($this->api->hasDocStart());
    }

    /**
     * @covers \Dallgoot\Yaml\API::isTagged
     */
    public function testIsTagged(): void
    {
        $this->assertFalse($this->api->isTagged());
        $this->api->addTag('!tagName');
        $this->assertTrue($this->api->isTagged());
    }
}
