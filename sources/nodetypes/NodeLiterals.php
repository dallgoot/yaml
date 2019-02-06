<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeLiterals extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        if (isset($nodeString[1]) && in_array($nodeString[1], ['-', '+'])) {
            $this->identifier = $nodeString[1];
        }
    }

    public function add(Node $child)
    {
        $realValue = new NodeScalar(ltrim($child->raw), $child->line);
        $this->value = new NodeList();
        parent::add($realValue);
    }

    public function isAwaitingChildren()
    {
        return is_null($this->value);
    }


    public static function litteralStripLeading(NodeList &$list)
    {
        $list->rewind();
        while ($list->bottom() instanceof NodeBlank) {//remove trailing blank
            $list->shift();
            $list->rewind();
        }
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
     * Builds a litteral (folded or not) or any NodeList that has YAML::RAW type (like a multiline value)
     * As per Documentation : 8.1.1.2. Block Chomping Indicator
     * Chomping controls how final line breaks and trailing empty lines are interpreted.
     * YAML provides three chomping methods:
     *   Clip (default behavior)  : FINAL_LINE_BREAK, NO TRAILING EMPTY LINES
     *   Strip (“-” chomping indicator)  NO FINAL_LINE_BREAK, NO TRAILING EMPTY LINES
     *   Keep (“+” chomping indicator)  FINAL_LINE_BREAK && TRAILING EMPTY LINES
     *
     * @param NodeList $list The children
     *
     * @return string    The litteral.
     * @todo   Example 6.1. Indentation Spaces  spaces must be considered as content
     */
    public function buildLitteral(NodeList &$list):string
    {
        $result = '';
        if ($this instanceof NodeLit) {
            return self::buildLitt($list);
        }
        if ($this instanceof NodeRaw) {
            return self::buildRaw($list);
        }
        if ($list->count()) {
            if ($this->modifier !== '+') {
                 self::litteralStripLeading($list);
                 self::litteralStripTrailing($list);
            }
            $first = $list->shift();
            $refIndent = $first->indent ?? 0;
            // $refSeparator = [ Y::RAW => '', Y::LITT => "\n", Y::LITT_FOLDED => ' '][$type];
            $refSeparator = ' ';
            $result = substr($first->raw, $first->indent);
            foreach ($list as $child) {
                if ($this instanceof NodeLitFolded) {
                    if($child->indent > $refIndent || ($child instanceof NodeBlank)) {
                        $separator = "\n";
                    } else {
                        $separator = !empty($result) && $result[-1] === "\n" ? '' : $refSeparator;
                    }
                } else {
                    $separator = $refSeparator;
                }
                $val = '';
                if ($child->value instanceof NodeList) {
                    $val = "\n".self::buildLitteral($child->value);
                } else {
                    if ($child instanceof NodeScalar) {
                        $val = $child->value;
                    } /*else {
                        $cursor = $child;
                        $val    = substr($child->raw, $child->indent);
                        // while ($cursor->value instanceof Node) {
                        //     $val .= substr($cursor->raw, $cursor->indent);
                        //     $cursor = $cursor->value;
                        // }
                        // $val .= $cursor->value;
                    }*/
                }
                $result .= $separator .$val;
            }
        }
        return $result.($this->modifier === '-' ? "" : "\n");
    }



}