<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 * @todo implement  Indentation indicator 8.1.1
 */
abstract class NodeLiterals extends Node
{
    abstract protected function getFinalString(NodeList $list):string;

    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        if (isset($nodeString[1]) && in_array($nodeString[1], ['-', '+'])) {
            $this->identifier = $nodeString[1];
        }
    }

    public function add(Node $child):Node
    {
        if (is_null($this->value)) $this->value = new NodeList();
        $candidate = $child;
        if (!isOneOf($child, ['NodeScalar', 'NodeBlank', 'NodeComment', 'NodeQuoted'])) {
            $candidate = new NodeScalar($child->raw, $child->line);
        }
        return parent::add($candidate);
    }

    protected static function litteralStripLeading(NodeList &$list)
    {
        $list->rewind();
        while ($list->bottom() instanceof NodeBlank) {//remove leading blank
            $list->shift();
            $list->rewind();
        }
        $list->rewind();
    }

    protected static function litteralStripTrailing(NodeList &$list)
    {
        $list->rewind();
        while ($list->top() instanceof NodeBlank) {//remove trailing blank
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
        if (!is_null($this->_tag)) {
            return TagFactory::transform($this->_tag, $this->value)->build($parent);
        }
        if (!is_null($this->value)) {
            $tmp = $this->getFinalString($this->value->filterComment());
            $result = $this->identifier === '-' ? $tmp : $tmp."\n";
        }
        if ($this->_parent instanceof NodeRoot) {
            $this->getRoot()->getYamlObject()->setText($result);
        } else {
            return $result;
        }
    }

    protected function getChildValue(Node $child, int $refIndent):string
    {
        $value = $child->value;
        $start = '';
        if (is_null($value)) {
            return $child instanceof NodeQuoted ? $child->build() : ltrim($child->raw);
        } elseif ($value instanceof Node) {
            if ($child instanceof NodeKey || $child instanceof NodeItem) {
                $start = ltrim($child->raw)."\n";
            }
            return $this->getFinalString(new NodeList($value), $refIndent);
        } elseif ($value instanceof NodeList) {
            if ($child instanceof NodeKey || $child instanceof NodeItem) {
                $start = ltrim($child->raw)."\n";
            }
            return $start.$this->getFinalString($value, $refIndent);
        }
        return '';
    }

    public function isAwaitingChild(Node $node):bool
    {
        return true;
    }
}