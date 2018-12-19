<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y, Regex as R, TypesBuilder as TB};

/**
 * Constructs the result (YamlObject or array) according to every Node and respecting value
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Builder
{
    public static $_root;
    private static $_debug;

    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param   Node   $_root      The root node : Node with Node->type === YAML::ROOT
     * @param   int   $_debug      the level of debugging requested
     *
     * @return array|YamlObject      list of documents or just one.
     * @todo  implement splitting on YAML::DOC_END also
     */
    public static function buildContent(Node $_root, int $_debug)
    {
        self::$_debug = $_debug;
        $totalDocStart = 0;
        $documents = [];
        $buffer = new NodeList();
        if ($_root->value instanceof NddeList) $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        foreach ($_root->value as $child) {
            if ($child->type & Y::DOC_START) {
                if(++$totalDocStart > 1){
                    $documents[] = self::buildDocument($buffer, count($documents));
                    $buffer = new NodeList($child);
                }
            } else {
                $buffer->push($child);
            }
        }
        $documents[] = self::buildDocument($buffer, count($documents));
        return count($documents) === 1 ? $documents[0] : $documents;
    }

    private static function buildDocument(NodeList $list, int $docNum):YamlObject
    {
        self::$_root = new YamlObject;
        try {
            $out = self::buildNodeList($list, self::$_root);
        } catch (\Exception $e) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum));
        }
        return $out;
    }

    /**
     * Generic function to distinguish between Node and NodeList
     *
     * @param Node|NodeList $node   The node.
     * @param mixed         $parent The parent
     *
     * @return mixed  ( description_of_the_return_value )
     */
    public static function build(object $node, &$parent = null)
    {
        if ($node instanceof NodeList) return self::buildNodeList($node, $parent);
        return self::buildNode($node, $parent);
    }

    /**
     * Builds a node list.
     *
     * @param NodeList $node   The node
     * @param mixed    $parent The parent
     *
     * @return mixed    The parent (object|array) or a string representing the NodeList.
     */
    public static function buildNodeList(NodeList $node, &$parent=null)
    {
        $node->forceType();
        if ($node->type & (Y::RAW | Y::LITTERALS)) {
            return self::buildLitteral($node, (int) $node->type);
        }
        $action = function ($child, &$parent, &$out) {
            self::build($child, $out);
        };
        if ($node->type & (Y::COMPACT_MAPPING|Y::MAPPING|Y::SET)) {
            $out = $parent ?? new \StdClass;
        } elseif ($node->type & (Y::COMPACT_SEQUENCE|Y::SEQUENCE)) {
            $out = $parent ?? [];
        } else {
            $out = '';
            $action = function ($child, &$parent, &$out) {
                if ($child->type & (Y::SCALAR|Y::QUOTED)) {
                    if ($parent) {
                        $parent->setText(self::build($child));
                    } else {
                        $out .= self::build($child);
                    }
                }
            };
        }
        foreach ($node as $child) {
            $action($child, $parent, $out);
        }
        if ($node->type & (Y::COMPACT_SEQUENCE|Y::COMPACT_MAPPING) && !empty($out)) {
            $out = new Compact($out);
        }
        return is_null($out) ? $parent : $out;
    }

    /**
     * Builds a node.
     *
     * @param Node    $node    The node of any Node->type
     * @param mixed  $parent  The parent
     *
     * @return mixed  The node value as Scalar, Array, Object or Null otherwise.
     */
    private static function buildNode(Node $node, &$parent)
    {
        extract((array) $node, EXTR_REFS);
        $actions = [Y::DIRECTIVE => 'buildDirective',
                    Y::ITEM      => 'buildItem',
                    Y::KEY       => 'buildKey',
                    Y::SET_KEY   => 'buildSetKey',
                    Y::SET_VALUE => 'buildSetValue',
                    Y::TAG       => 'buildTag',
        ];
        if (isset($actions[$type])) {
            return TB::{$actions[$type]}($node, $parent);
        } elseif ($type & Y::COMMENT) {
            self::$_root->addComment($line, $value);
        } elseif ($type & (Y::COMPACT_MAPPING|Y::COMPACT_SEQUENCE)) {
            return self::buildNodeList($value, $parent);
        } elseif ($type & (Y::REF_DEF | Y::REF_CALL)) {
            return TB::buildReference($node, $parent);
        } elseif ($value instanceof Node) {
            return self::buildNode($value, $parent);
        } else {
            return Node2PHP::get($node);
        }
    }


    /**
     * Builds a litteral (folded or not) or any NodeList that has YAML::RAW type (like a multiline value)
     *
     * @param      NodeList  $children  The children
     * @param      integer   $type      The type
     *
     * @return     string    The litteral.
     * @todo : Example 6.1. Indentation Spaces  spaces must be considered as content
     */
    private static function buildLitteral(NodeList $list, int $type = Y::RAW):string
    {
        $list->rewind();
        $refIndent = $list->current()->indent;
        //remove trailing blank
        while ($list->top()->type & Y::BLANK) $list->pop();
        $result = '';
        $separator = [ Y::RAW => '', Y::LITT => "\n", Y::LITT_FOLDED => ' '][$type];
        foreach ($list as $child) {
            if ($child->value instanceof NodeList) {
                $result .= self::buildLitteral($child->value, $type).$separator;
            } else {
                self::setLiteralValue($child, $result, $refIndent, $separator, $type);
            }
        }
        return rtrim($result);
    }

    private static function setLiteralValue(Node $child, string &$result, int $refIndent, string $separator, int $type)
    {
        $val = $child->type & (Y::SCALAR) ? $child->value : substr($child->raw, $refIndent);
        if ($type & Y::LITT_FOLDED && ($child->indent > $refIndent || ($child->type & Y::BLANK))) {
            if ($result[-1] === $separator)
                $result[-1] = "\n";
            if ($result[-1] === "\n")
                $result .= $val;
            return;
        }
        $result .= $val.$separator;
    }

}
