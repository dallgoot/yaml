<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
abstract class NodeLiterals extends Node
{
    abstract function getFinalString(NodeList $list):string;

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
        if (!isOneOf($child, ['NodeScalar', 'NodeBlank', 'NodeComment'])) {
            $candidate = new NodeScalar($child->raw, $child->line);
        } else if ($child instanceof NodeQuoted) {
            $candidate = new NodeScalar($child->build(), $child->line);
        }
        return parent::add($candidate);
    }

    // public function isAwaitingChildren()
    // {
    //     return true;//is_null($this->value);
    // }

    public static function litteralStripLeading(NodeList &$list)
    {
        $list->rewind();
        while ($list->bottom() instanceof NodeBlank) {//remove trailing blank
            $list->shift();
            $list->rewind();
        }
        $list->rewind();
    }

    public static function litteralStripTrailing(NodeList &$list)
    {
        $list->rewind();
        while ($list->top() instanceof NodeBlank) {//remove trailing blank
            $list->pop();
        }
        $list->rewind();
    }

    /**
     * Builds a litteral (folded or not) or any NodeList
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

    public function getChildValue(Node $child, int $refIndent):string
    {
        if ($child instanceof NodeScalar ) {
            return $child->build();
        } else {
            $value = $child->value;
            if ($value instanceof Node) {
                if ($value->indent > 0) {
                    return substr($child->raw."\n".$this->getChildValue($value, $refIndent), $refIndent);
                } else {
                    return substr($child->raw, $refIndent);
                }
            } elseif ($value instanceof NodeList) {
                $start = '';
                if ($child instanceof NodeKey || $child instanceof NodeItem) {
                    $start = substr($child->raw, $refIndent)."\n";
                }
                return $start.$this->getFinalString($value, 0);
            }
        }
        return '';
    }
}