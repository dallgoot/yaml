<?php

namespace Test\Dallgoot\Yaml;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Node;
use Dallgoot\Yaml\NodeAnchor;
use Dallgoot\Yaml\NodeBlank;
use Dallgoot\Yaml\NodeComment;
use Dallgoot\Yaml\NodeCompactMapping;
use Dallgoot\Yaml\NodeCompactSequence;
use Dallgoot\Yaml\NodeDirective;
use Dallgoot\Yaml\NodeDocEnd;
use Dallgoot\Yaml\NodeDocStart;
use Dallgoot\Yaml\NodeItem;
use Dallgoot\Yaml\NodeJSON;
use Dallgoot\Yaml\NodeKey;
use Dallgoot\Yaml\NodeLitFolded;
use Dallgoot\Yaml\NodeLit;
use Dallgoot\Yaml\NodePartial;
use Dallgoot\Yaml\NodeQuoted;
use Dallgoot\Yaml\NodeScalar;
use Dallgoot\Yaml\NodeSetKey;
use Dallgoot\Yaml\NodeSetValue;
use Dallgoot\Yaml\NodeTag;

/**
 * Class NodeFactoryTest.
 *
 * @author Stephane Rebai <stephane.rebai@gmail.com>.
 * @license https://opensource.org/licenses/MIT The MIT license.
 * @link https://github.com/dallgoot/yaml
 * @since File available since Release 1.0.0
 *
 * @covers \Dallgoot\Yaml\NodeFactory
 */
class NodeFactoryTest extends TestCase
{
    /**
     * @var NodeFactory $nodeFactory An instance of "NodeFactory" to test.
     */
    private $nodeFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @todo Maybe add some arguments to this constructor */
        $this->nodeFactory = new NodeFactory();
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::get
     */
    public function testGet(): void
    {
        $this->assertTrue($this->nodeFactory::get('', 1) instanceof NodeBlank, 'Not a NodeBlank');
        $this->assertTrue($this->nodeFactory::get('    ...', 1) instanceof NodeDocEnd, 'Not a NodeDocEnd');
        $this->assertTrue($this->nodeFactory::get('  key : value', 1) instanceof NodeKey, 'Not a NodeKey');
        $this->assertTrue($this->nodeFactory::get('$qsd = 3213', 1) instanceof NodeScalar, 'Not a NodeScalar');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onSpecial
     */
    public function testOnSpecial(): void
    {
        $reflector = new \ReflectionClass($this->nodeFactory);
        $method = $reflector->getMethod('onSpecial');
        $method->setAccessible(true);
        $nodeString = '#qsd = 3213';
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodeComment, 'Not a NodeComment');
        $nodeString = '%YAML 1.2';
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodeDirective, 'Not a NodeDirective');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onQuoted
     */
    public function testOnQuoted(): void
    {
        $reflector = new \ReflectionClass($this->nodeFactory);
        $method = $reflector->getMethod('onQuoted');
        $method->setAccessible(true);
        $nodeString = '    "double quoted" ';
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodeQuoted,
                        'Not a NodeQuoted');
        $nodeString = " 'simple quoted'   ";
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodeQuoted,
                        'Not a NodeQuoted');
        $nodeString = " 'simple partial   ";
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodePartial,
                        'Not a NodePartial');
        $nodeString = '" double partial  ';
        $this->assertTrue($method->invoke(null,$nodeString[0], $nodeString, 1) instanceof NodePartial,
                        'Not a NodePartial');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onSetElement
     */
    public function testOnSetElement(): void
    {
        $reflector = new \ReflectionClass($this->nodeFactory);
        $method = $reflector->getMethod('onSetElement');
        $method->setAccessible(true);
        $nodeString = '    ? some setkey ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeSetKey,
                'Not a NodeSetKey');
        $nodeString = '    : some setvalue ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeSetValue,
                'Not a NodeSetValue');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onCompact
     */
    public function testOnCompact(): void
    {
        $method = new \ReflectionMethod($this->nodeFactory, 'onCompact');
        $method->setAccessible(true);
        $nodeString = '["a","b","c"]';
        $this->assertTrue($method->invoke(null, '', $nodeString, 1) instanceof NodeJSON,
                'Not a NodeJSON');
        $nodeString = '{"key":"value","key1":"value1"}';
        $this->assertTrue($method->invoke(null, '', $nodeString, 1) instanceof NodeJSON,
                'Not a NodeJSON');
        $nodeString = '{  key :  value ,  key1  : value1  }';
        $this->assertTrue($method->invoke(null, '', $nodeString, 1) instanceof NodeCompactMapping,
                'Not a NodeCompactMapping');
        $nodeString = '[a,b,c]';
        $this->assertTrue($method->invoke(null, '', $nodeString, 1) instanceof NodeCompactSequence,
                'Not a NodeCompactSequence');
        $nodeString = ' { a: b, ';
        $result = $method->invoke(null, '', $nodeString, 1);
        $this->assertTrue($result instanceof NodePartial,
                'Not a NodeScalar');

    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onHyphen
     */
    public function testOnHyphen(): void
    {
        $reflector = new \ReflectionClass($this->nodeFactory);
        $method = $reflector->getMethod('onHyphen');
        $method->setAccessible(true);
        $nodeString = '- ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeItem,
                'Not a NodeItem');
        $nodeString = '-- ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeScalar,
                'Not a NodeScalar');
        $nodeString = '---';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeDocStart,
                'Not a NodeDocStart');
        $nodeString = '  - simpleitem  ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeItem,
                'Not a NodeItem');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onNodeAction
     */
    public function testOnNodeAction(): void
    {
        $method = new \ReflectionMethod($this->nodeFactory, 'onNodeAction');
        $method->setAccessible(true);
        $nodeString = '***';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeScalar,
                'Not a NodeScalar');
        $nodeString = '&emptyanchor';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeAnchor,
                'Not a NodeAnchor'.get_class($method->invoke(null, trim($nodeString)[0], $nodeString, 1)));
        $nodeString = '&anchor [1,2,3]';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeAnchor,
                'Not a NodeAnchor');
        $nodeString = '*anchorcall ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeAnchor,
                'Not a NodeAnchor');
        $nodeString = '!!emptytag';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeTag,
                'Not a NodeTag');
        $nodeString = '!!str   345  ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeTag,
                'Not a NodeTag');
    }


    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onLiteral
     */
    public function testOnLiteral(): void
    {
        $method = new \ReflectionMethod($this->nodeFactory, 'onLiteral');
        $method->setAccessible(true);
        $nodeString = '  |-   ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeLit,
                'Not a NodeLit');
        $nodeString = '  >+   ';
        $this->assertTrue($method->invoke(null, trim($nodeString)[0], $nodeString, 1) instanceof NodeLitFolded,
                'Not a NodeLitFolded');
    }

    /**
     * @covers \Dallgoot\Yaml\NodeFactory::onLiteral
     */
    public function testOnLiteralFail(): void
    {
        $method = new \ReflectionMethod($this->nodeFactory, 'onLiteral');
        $method->setAccessible(true);
        $nodeString = ' x ';
        $this->expectException(\ParseError::class);
        $method->invoke(null, trim($nodeString)[0], $nodeString, 1);
    }
}
