<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeComment;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeRoot;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\YamlObject;

/**
 * Class NodeCommentTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeComment
 */
class NodeCommentTest extends TestCase
{
    /**
     * @var NodeComment $nodeComment An instance of "NodeComment" to test.
     */
    private $nodeComment;

    private $commentLine = 5;
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeComment = new NodeComment('#this is a comment for test', $this->commentLine);
    }

    /**
     * @covers \Dallgoot\Yaml\NodeComment::specialProcess
     */
    public function testSpecialProcess(): void
    {
        $keyNode = new NodeKey('  key: keyvalue',1);
        $rootNode = new NodeRoot();
        $rootNode->add($keyNode);
        $blankBuffer = [];
        $this->assertTrue($this->nodeComment->specialProcess($keyNode, $blankBuffer));
    }

    /**
     * @covers \Dallgoot\Yaml\NodeComment::build
     */
    public function testBuild(): void
    {
        $yamlObject = new YamlObject;
        $rootNode = new NodeRoot;
        $reflector = new \ReflectionClass($rootNode);
        $method = $reflector->getMethod('buildFinal');
        $method->setAccessible(true);
        $method->invoke($rootNode, $yamlObject);
        $rootNode->add($this->nodeComment);
        $this->nodeComment->build();
        $this->assertEquals($yamlObject->getComment($this->commentLine), $this->nodeComment->raw);
    }
}
