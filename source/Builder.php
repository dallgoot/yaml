<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{NodeList, Yaml as Y, Regex as R, TypesBuilder as TB};

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

    const INVALID_DOCUMENT = "DOCUMENT %d is invalid,";

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
        $docStarted = 0;
        $documents = [];
        $buffer = new NodeList();
        if ($_root->value instanceof NodeList) {
            $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        } else {
            die("NOT A LIST");
        }
        foreach ($_root->value as $child) {
            if ($child->type & Y::DOC_END && $child !== $_root->value->top()) {
                $buffer->push($child);
                $documents[] = self::buildDocument($buffer, count($documents));
                $buffer = new NodeList();
            } elseif ($child->type & Y::DOC_START) {
                if ($buffer->count() === 0) {
                    $buffer->push($child);
                } else {
                    if (in_array($buffer->getTypes(), [Y::COMMENT, Y::DIRECTIVE])) {
                        $buffer->push($child);
                    } else {
                        $documents[] = self::buildDocument($buffer, count($documents));
                        $buffer = new NodeList($child);
                    }
                }
            } else {
                $buffer->push($child);
            }
        }
        $documents[] = self::buildDocument($buffer, count($documents));
        return count($documents) === 1 ? $documents[0] : $documents;
    }

    private static function buildDocument(NodeList $list, int $docNum):YamlObject
    {var_dump(__METHOD__);
        self::$_root = new YamlObject;
        try {
            $out = self::buildNodeList($list, self::$_root);
            if (is_string($out)) {
                $out = self::$_root->setText($out);
            }
        } catch (\Exception $e) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum)." ".$e->getMessage()."\n ".$e->getFile().':'.$e->getLine());
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
    public static function buildNodeList(NodeList $list, &$parent=null)
    {
        $list->forceType();
        $action = function ($child, &$parent, &$out) {
            self::build($child, $out);
        };
        $list->type = $list->type ?? Y::RAW;
        if ($list->type & (Y::RAW|Y::LITTERALS)) {
            $tmp = self::buildLitteral($list, $list->type);//var_dump("ICI1",$list, $tmp);
            return $tmp;
        } elseif ($list->type & (Y::COMPACT_MAPPING|Y::MAPPING|Y::SET)) {//var_dump("ICI2");
            $out = $parent ?? new \StdClass;
        } elseif ($list->type & (Y::COMPACT_SEQUENCE|Y::SEQUENCE)) {//var_dump("ICI3");
            $out = $parent ?? [];
        } else {
            $out = null;//var_dump("ICI");
            $action = function ($child, &$parent, &$out) {
                if ($child->type & (Y::SCALAR|Y::QUOTED|Y::DOC_START)) {
                    $out .= Node2PHP::get($child);
                } else {
                    self::build($child);//var_dump("HERE");
                }
                // var_dump("out:".$out);
            };
        }
        foreach ($list as $child) {
            $action($child, $parent, $out);
        }
        if ($list->type & (Y::COMPACT_SEQUENCE|Y::COMPACT_MAPPING) && !empty($out)) {
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
        // } elseif ($type & Y::DOC_START) {
        //     if (!is_null($value)) {
        //         return self::build($value->value, $self::$_root);
        //     }
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
    private static function buildLitteral(NodeList &$list, int $type = Y::RAW):string
    {//var_dump(__METHOD__,$list);
        $result = '';
        $list->rewind();
        $refIndent = $list->count() > 0 ? $list->current()->indent : 0;
        if($list->count()) {
            //remove trailing blank
            while ($list->top()->type & Y::BLANK) $list->pop();
            $separator = [ Y::RAW => '', Y::LITT => "\n", Y::LITT_FOLDED => ' '][$type];
            foreach ($list as $child) {
                if ($child->value instanceof NodeList) {
                    $result .= self::buildLitteral($child->value, $type).$separator;
                } else {
                    self::setLiteralValue($child, $result, $refIndent, $separator, $type);
                }
            }
        }
        return rtrim($result);
    }

    private static function setLiteralValue(Node $node, string &$result, int $refIndent, string $separator, int $type)
    {
        if ($node->type & Y::SCALAR) {
            $val = $node->value;
        } else {
            if ($node->value instanceof Node && !($node->value->type & Y::SCALAR) && preg_match('/(([^:]+:)|( *-)) *$/', $node->raw)) {
                $childValue = '';
                self::setLiteralValue($node->value, $childValue, $refIndent, $separator, $type);
                // $val = substr($node->raw.$separator.$childValue, $refIndent);
                // $val = substr($node->raw, $refIndent).$separator.self::buildLitteral(new NodeList($node->value), $type);
                $val = substr($node->raw, $refIndent).$separator.$childValue;
            } else {
                $val = substr($node->raw, $refIndent);
            }
        }
        if ($type & Y::LITT_FOLDED && ($node->indent > $refIndent || ($node->type & Y::BLANK))) {
            if ($result[-1] === $separator)
                $result[-1] = "\n";
            if ($result[-1] === "\n")
                $result .= $val;
            return;
        }
        $result .= $val.$separator;
    }

}
