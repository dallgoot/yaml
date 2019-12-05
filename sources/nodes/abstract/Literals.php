<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\Tagged;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 * @todo implement  Indentation indicator 8.1.1
 */
abstract class Literals extends NodeGeneric
{
    /** @var NodeList */
    public $value;
    public $identifier;
    public $tag;
    protected $_parent;

    abstract protected function getFinalString(NodeList $list, int $refIndent = null):string;

    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        if (isset($nodeString[1]) && in_array($nodeString[1], ['-', '+'])) {
            $this->identifier = $nodeString[1];
        }
    }

    public function add(NodeGeneric $child):NodeGeneric
    {
        if (is_null($this->value)) $this->value = new NodeList();
        $candidate = $child;
        if (!$child->isOneOf('Scalar', 'Blank', 'Comment', 'Quoted')) {
            $candidate = new Scalar((string) $child->raw, $child->line);
        }
        return parent::add($candidate);
    }

    protected static function litteralStripLeading(NodeList &$list)
    {
        $list->rewind();
        while (!$list->isEmpty() && $list->bottom() instanceof Blank) {//remove leading blank
            $list->shift();
        }
        $list->rewind();
    }

    protected static function litteralStripTrailing(NodeList &$list)
    {
        $list->rewind();
        while (!$list->isEmpty() && $list->top() instanceof Blank) {//remove trailing blank
            $list->pop();
        }
        $list->rewind();
    }

    /**
     * Builds a litteral (folded or not)
     * As per Documentation : 8.1.1.2. Block Chomping Indicator
     * Chomping controls how final line breaks and trailing empty lines are interpreted.
     * YAML provides three chomping methods:
     *   Clip (default behavior)  : FINAL_LINE_BREAK, NO TRAILING EMPTY LINES
     *   Strip (“-” chomping indicator)  NO FINAL_LINE_BREAK, NO TRAILING EMPTY LINES
     *   Keep (“+” chomping indicator)  FINAL_LINE_BREAK && TRAILING EMPTY LINES
     */
    public function build(&$parent = null)
    {
        $result = '';
        if (!is_null($this->tag)) {
            $output = TagFactory::transform($this->tag, $this->value);
            return $output instanceof Tagged ? $output : $output->build($parent);
        }
        if (!is_null($this->value)) {
            $tmp = $this->getFinalString($this->value->filterComment());
            $result = $this->identifier === '-' ? $tmp : $tmp."\n";
        }
        if ($this->_parent instanceof Root) {
            $this->_parent->getYamlObject()->setText($result);
            return null;
        } else {
            return $result;
        }
    }

    /**
     * Gets the correct string for child value.
     *
     * @param      object         $child      The child
     * @param      int|null       $refIndent  The reference indent
     *
     * @return     string  The child value.
     * @todo       double check behaviour for KEY and ITEM
     */
    protected function getChildValue($child, $refIndent=0):string
    {
        $value = $child->value;
        $start = '';
        if (is_null($value)) {
            if ($child instanceof Quoted) {
                return $child->build();
            } elseif ($child instanceof Blank) {
                return '';
            } else {
                return ltrim($child->raw);
            }
        } elseif ($value instanceof Scalar) {
            $value = new NodeList($value);

        } elseif ($value instanceof NodeList && !($child instanceof Scalar)) {
            $start = ltrim($child->raw)."\n";
        }
        return $start.$this->getFinalString($value, $refIndent);
    }

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        return true;
    }
}