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
     * @param Node $_root  The root node : Node with Node->type === YAML::ROOT
     * @param int  $_debug the level of debugging requested
     *
     * @return array|YamlObject      list of documents or just one.
     * @todo   implement splitting on YAML::DOC_END also
     */
    public static function buildContent(Node $_root, int $_debug)
    {
        self::$_debug = $_debug;
        $docStarted = 0;
        $documents = [];
        $buffer = new NodeList();
        if ($_root->value instanceof Node) {
            $_root->value = new NodeList($_root->value);
        }
        $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
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
        try {
            $documents[] = self::buildDocument($buffer, count($documents));
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception(__METHOD__, 1, $e);
        }
        return count($documents) === 1 ? $documents[0] : $documents;
    }

    /**
     *  Builds the tree of Node for this document (as NodeList)
     *
     * @param NodeList $list   the list of nodes that constitutes the current document
     * @param int      $docNum the index (starts @ 0) of this document in the whole YAML content provided to self::buildContent
     *
     * @return YamlObject the YAML document as an object
     */
    private static function buildDocument(NodeList $list, int $docNum):YamlObject
    {//var_dump(__METHOD__);
        self::$_root = new YamlObject;
        try {
            $out = self::buildNodeList($list, self::$_root);
            if (is_string($out)) {
                $out = self::$_root->setText($out);
            }
        } catch (\Exception $e) {
            // throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum)." ".$e->getMessage()."\n ".$e->getFile().':'.$e->getLine(), 2, $e);
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum), 2, $e);
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
     * @param NodeList $list   The node
     * @param mixed    $parent The parent
     *
     * @return mixed The parent (object|array) or a string representing the NodeList.
     */
    public static function buildNodeList(NodeList $list, &$parent=null)
    {
        $action = function ($child, &$parent, &$out) {
            self::build($child, $out);
        };
        $list->forceType();
        $list->type = $list->type ?? Y::RAW;
        if ($list->type & (Y::RAW|Y::LITTERALS)) {
            return self::buildLitteral($list, $list->type);
        } elseif ($list->type & (Y::COMPACT_MAPPING|Y::MAPPING|Y::SET)) {//var_dump("ICI2");
            $out = $parent ?? new \StdClass;
        } elseif ($list->type & (Y::COMPACT_SEQUENCE|Y::SEQUENCE)) {//var_dump("ICI3");
            $out = $parent ?? [];
        } else {
            $out = '';//var_dump("ICI");
            $action = function ($child, &$parent, &$out) {
                if ($child->type & Y::DOC_START) {
                    if (is_scalar($child->value)) {
                        $parent->setText(Node2PHP::get($child));
                    } elseif ($child->value instanceof Node && $child->value->type & Y::TAG){
                        $parent->addTag($child->value->identifier);
                    } else {
                        $parent->setText(self::build($child->value));
                    }
                } else {
                    $out .= ','.self::build($child);
                }
            };
        }
        foreach ($list as $child) {
            $action($child, $parent, $out);
        }
        if ($list->type & (Y::COMPACT_SEQUENCE|Y::COMPACT_MAPPING) && !empty($out)) {
            $out = new Compact($out);
        }
        if (is_string($out)) {
            $result = implode(explode(',', $out));
            $out = $result === '' ? null : Node2PHP::getScalar($result);
        }
        return is_null($out) ? $parent : $out;
    }

    /**
     * Builds a node.
     *
     * @param Node  $node   The node of any Node->type
     * @param mixed $parent The parent
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
        } elseif ($value instanceof NodeList) {
            return self::buildNodeList($value, $parent);
        } else {
            return Node2PHP::get($node);
        }
    }


    /**
     * Builds a litteral (folded or not) or any NodeList that has YAML::RAW type (like a multiline value)
     *
     * @param NodeList $list The children
     * @param integer  $type The type
     *
     * @return string    The litteral.
     * @todo   Example 6.1. Indentation Spaces  spaces must be considered as content
     */
    public static function buildLitteral(NodeList &$list, int $type = Y::RAW):string
    {//var_dump(__METHOD__,$list);
        $result = '';
        if ($list->count()) {
            $list->rewind();
            $refIndent = $list->current()->indent ?? 0;
            while ($list->top()->type & Y::BLANK) //remove trailing blank
                $list->pop();
            foreach ($list as $child) {
                $separator = [ Y::RAW => '', Y::LITT => "\n", Y::LITT_FOLDED => ' '][$type];
                if ($type & Y::LITT_FOLDED && ($child->indent > $refIndent || ($child->type & Y::BLANK))) {
                    $separator = "\n";
                }
                $val = '';
                if ($child->value instanceof NodeList) {
                    $val = $separator.self::buildLitteral($child->value, $type);
                } else {
                    if ($child->type & Y::SCALAR) {
                        $val = $node->value;
                    } else {
                        $cursor = $child;
                        $val    = $child->raw;
                        while ($cursor->value instanceof Node) {
                            $val .= $cursor->raw;
                            $cursor = $cursor->value;
                        }
                        // $val .= $cursor->value;
                    }
                }
                $result .= $separator.$val;
            }
        }
        return ltrim($result);
    }

    /**
     * Sets the literal value.
     *
     * @param Node    $node      The node
     * @param string  $result    The result
     * @param integer $refIndent The reference indent
     * @param string  $separator The separator
     * @param integer $type      The type
     *
     * @return null
     */
    private static function setLiteralValue(Node $node, string &$result, int $refIndent, string $separator, int $type)
    {
        if ($node->type & Y::SCALAR) {
            $val = $node->value;
        } else {
            // if ($node->value instanceof Node && !($node->value->type & Y::SCALAR) && preg_match('/(([^:]+:)|( *-)) *$/', $node->raw)) {
            //     $childValue = '';
            //     self::setLiteralValue($node->value, $childValue, $refIndent, $separator, $type);
            //     $val = substr($node->raw, $node->indent).$separator.$childValue;
            // }
            // $val = substr($node->raw, $node->indent);
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
