<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y, Regex as R};

/**
 * Constructs the result (YamlObject or array) according to every Node and respecting value
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Builder
{
    private static $_root;
    private static $_debug;

    const ERROR_NO_KEYNAME = self::class.": key has NO IDENTIFIER on line %d";
    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";

    /**
     * Generic function to distinguish between Node and NodeList
     *
     * @param Node|NodeList $node   The node.
     * @param mixed         $parent The parent
     *
     * @return mixed  ( description_of_the_return_value )
     */
    private static function build(object $node, &$parent = null)
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
    private static function buildNodeList(NodeList $node, &$parent)
    {
        if ($node->type & (Y::RAW | Y::LITTERALS)) {
            return self::buildLitteral($node, $node->type);
        }
        $p = $parent;
        switch ($node->type) {
            case Y::MAPPING: // fall through
            case Y::SET:      $p = new \StdClass; break;
            case Y::SEQUENCE: $p = [];break;
        }
        $out = null;
        foreach ($node as $child) {
            $result = self::build($child, $p);
            if (!is_null($result)) {
                if (is_string($result)) {
                    $out .= $result.' ';
                } else {
                    return $result;
                }
            }
        }
        return is_null($out) ? $p : rtrim($out);
    }

    /**
     * Builds a node.
     *
     * @param Node    $node    The node of any Node->type
     * @param mixed  $parent  The parent
     *
     * @return mixed  The node value as scalar, array or object or null to otherwise.
     */
    private static function buildNode(Node $node, &$parent)
    {
        extract((array) $node, EXTR_REFS);
        if ($type & (Y::REF_DEF | Y::REF_CALL)) {
            if (is_object($value)) {
                $tmp = self::build($value, $parent) ?? $parent;
            } else {
                $tmp = Node2PHP::get($node);
            }
            if ($type === Y::REF_DEF) self::$_root->addReference($identifier, $tmp);
            return self::$_root->getReference($identifier);
        }

        if ($type & Y::COMMENT) self::$_root->addComment($node->line, $node->value);
        $typesActions = [Y::DIRECTIVE => 'buildDirective',
                         Y::ITEM      => 'buildItem',
                         Y::KEY       => 'buildKey',
                         Y::SET_KEY   => 'buildSetKey',
                         Y::SET_VALUE => 'buildSetValue',
                         Y::TAG       => 'buildTag',
        ];
        if (isset($typesActions[$type])) {
            return self::{$typesActions[$type]}($node, $parent);
        }
        return is_object($value) ? self::build($value, $parent) : Node2PHP::get($node);
    }

    /**
     * Builds a key and set the property + value to the given parent
     *
     * @param Node $node       The node with type YAML::KEY
     * @param object|array $parent       The parent
     *
     * @throws \ParseError if Key has no name(identifier) Note: empty string is allowed
     * @return null
     */
    private static function buildKey(Node $node, &$parent)
    {
        extract((array) $node, EXTR_REFS);
        if (is_null($identifier)) {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $line));
        } else {
            if (is_array($parent)) {
                $target = &$parent[$identifier];
            } else {
                $target = &$parent->{$identifier};
            }
            if ($value instanceof Node && ($value->type & (Y::KEY|Y::SET_KEY|Y::SET_KEY|Y::ITEM))) {
                $target = $value->type & Y::ITEM ? [] : new \StdClass;
                self::build($value, $target);
            } elseif (is_object($value)) {
                if (is_null($value->type) && $value->getTypes() & Y::SCALAR && !($value->getTypes() & Y::COMMENT)) {
                    $target = self::buildLitteral($value, Y::LITT_FOLDED);
                } else {
                    $target = self::build($value, $target);
                }
            } else {
                $target = Node2PHP::get($node);
            }
        }
    }

    /**
     * Builds an item. Adds the item value to the parent array|Iterator
     *
     * @param      Node        $node    The node with type YAML::ITEM
     * @param      array|\Iterator      $parent  The parent
     *
     * @throws     \Exception  if parent is another type than array or object Iterator
     * @return null
     */
    private static function buildItem(Node $node, &$parent)
    {
        if (!is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an Iterable not ".(is_object($parent) ? get_class($parent) : gettype($parent)), 1);
        }
        if ($node->value instanceof Node && $node->value->type & Y::KEY) {
            self::build($node->value, $parent);
        } else {
            $parent[] = is_null($node->value) ? null : self::build($node->value, $parent);
        }
    }

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     * TODO: implement splitting on YAML::DOC_END also
     *
     * @param   Node   $_root      The root node : Node with Node->type === YAML::ROOT
     * @param   int   $_debug      the level of debugging requested
     *
     * @return array|YamlObject      list of documents or juste one.
     */
    public static function buildContent(Node $_root, int $_debug)
    {
        self::$_debug = $_debug;
        $totalDocStart = 0;
        $documents = [];
        if ($_root->value instanceof Node) {
            $q = new NodeList;
            $q->push($_root->value);
            return self::buildDocument($q, 0);
        }
        $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        foreach ($_root->value as $child) {
            if ($child->type & Y::DOC_START) $totalDocStart++;
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0;
            if (!isset($documents[$currentDoc])) $documents[$currentDoc] = new NodeList();
            $documents[$currentDoc]->push($child);
        }
        $content = array_map([self::class, 'buildDocument'], $documents, array_keys($documents));
        return count($content) === 1 ? $content[0] : $content;
    }

    /**
     * Builds a document. Basically a NodeList of children
     *
     * @param      NodeList     $list   The list
     * @param      integer      $key    The key
     *
     * @throws     \ParseError  (description)
     *
     * @return     YamlObject   The document as the separated part (by YAML::DOC_START) inside a whole YAML content
     */
    private static function buildDocument(NodeList $list, int $key):YamlObject
    {
        self::$_root = new YamlObject();
        $childTypes = $list->getTypes();
        $isaMapping  = (bool) (Y::KEY | Y::MAPPING) & $childTypes;
        $isaSequence = (bool) Y::ITEM & $childTypes;
        $isaSet      = (bool) Y::SET_VALUE & $childTypes;
        if ($isaMapping && $isaSequence) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $key));
        } else {
            switch (true) {
                case $isaSequence: $list->type = Y::SEQUENCE;break;
                case $isaSet:      $list->type = Y::SET;break;
                default:           $list->type = Y::MAPPING;
            }
        }
        $string = '';
        foreach ($list as $child) {
            $result = self::build($child, self::$_root);
            if (is_string($result)) {
                $string .= $result.' ';
            }
        }
        if (!empty($string)) {
            self::$_root->setText(rtrim($string));
        }
        return self::$_root;
    }

    /**
     * Builds a litteral (folded or not) or any NodeList that has YAML::RAW type (like a multiline value)
     *
     * @param      NodeList  $children  The children
     * @param      integer   $type      The type
     *
     * @return     string    The litteral.
     */
    private static function buildLitteral(NodeList $children, int $type):string
    {
        $lines = [];
        $children->rewind();
        $refIndent = $children->current()->indent;
        // $lastChild = $children->pop();
        // $hasComment = strpos($lastChild->value, ' #');
        // if (is_int($hasComment)) {
        //     $children->push(new Node(trim(substr($lastChild->value, 0, $hasComment)), $lastChild->line));
        //     self::$_root->addComment($lastChild->line, trim(substr($lastChild->value, $hasComment)));//keep the '#' to note that it is NOT a fulline comment;
        // } else {
        //     $children->push($lastChild);
        // }
        //remove trailing blank nodes
        $max = $children->count() - 1;
        while ($children->offsetGet($max)->type & Y::BLANK) {
            $children->offsetUnset($max);
            $max = $children->count() - 1;
        }
        $children->rewind();
        // TODO : Example 6.1. Indentation Spaces  spaces must be considered as content
        foreach ($children as $child) {
            if ($child->value instanceof NodeList) {
                $lines[] = self::buildLitteral($child->value, $type);
            } else {
                $prefix = '';
                if ($type & Y::LITT_FOLDED && ($child->indent > $refIndent || ($child->type & Y::BLANK))) {
                    $prefix = "\n";
                }
                if (!($child->type & (Y::SCALAR|Y::BLANK))) {
                    switch ($child->type) {
                        case Y::ITEM:    $child->value = '- '.$child->value;break;
                        case Y::COMMENT: $child->value = '# '.$child->value;break;
                        default: //die(__METHOD__.Y::getName($child->type));
                    }
                }
                if (is_object($child->value)) {
                    // var_dump($child->$value);
                    throw new \ParseError(__METHOD__.':'.get_class($child->value), 1);
                    // die(__METHOD__);
                }
                $lines[] = $prefix.$child->value;
            }
        }
        if ($type & Y::RAW)         return implode('',   $lines);
        if ($type & Y::LITT)        return implode("\n", $lines);
        if ($type & Y::LITT_FOLDED) return preg_replace(['/ +(\n)/','/(\n+) +/'], "$1", implode(' ',  $lines));
        return '';
    }

    /**
     * Builds a set key.
     *
     * @param      Node        $node    The node of type YAML::SET_KEY.
     * @param      object      $parent  The parent
     *
     * @throws     \Exception  if a problem occurs during serialisation (json format) of the key
     */
    private function buildSetKey(Node $node, &$parent)
    {
        $built = self::build($node->value, $parent);
        $stringKey = is_string($built) && Regex::isProperlyQuoted($built) ? trim($built, '\'" '): $built;
        $key = json_encode($stringKey, JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
        // if (empty($key)) throw new \Exception("Cant serialize complex key: ".var_export($node->value, true), 1);
        $parent->{trim($key, '\'" ')} = null;
    }

    /**
     * Builds a set value.
     *
     * @param      Node    $node    The node of type YAML::SET_VALUE
     * @param      object  $parent  The parent (the document object or any previous object created through a mapping key)
     */
    private function buildSetValue(Node $node, &$parent)
    {
        $prop = array_keys(get_object_vars($parent));
        $key = end($prop);
        if ($node->value->type & (Y::ITEM|Y::KEY|Y::SEQUENCE|Y::MAPPING)) {
            $p = $node->value->type === Y::ITEM ? [] : new \StdClass;
            self::build($node->value, $p);
        } else {
            $p = self::build($node->value, $parent->{$key});
        }
        $parent->{$key} = $p;
    }

    /**
     * Builds a tag and its value (also built) and encapsulates them in a Tag object.
     *
     * @param      Node    $node    The node of type YAML::TAG
     * @param      mixed  $parent  The parent
     *
     * @return     Tag|null     The tag object of class Dallgoot\Yaml\Tag.
     */
    private function buildTag(Node $node, &$parent)
    {
        $list = $node->value;
        $current = $list->current();
        if ((is_object($parent) || is_array($parent)) && $current->type & Y::KEY) {
            self::buildKey($list, $parent);
            return;
        }
        if ($parent === self::$_root) {
            $parent->addTag($node->identifier);
            return self::buildNodeList($list, $parent);
        }
        //TODO: have somewhere a list of common tags and their treatment
        if (in_array($node->identifier, ['!binary', '!str'])) {
            $list->type = Y::RAW;
        }
        return new Tag($node->identifier, $list->count === 0 ? null : self::buildNodeList($list, $parent));
    }

    /**
     * Builds a directive. NOT IMPLEMENTED YET
     *
     * @param      Node  $node    The node
     * @param      mixed  $parent  The parent
     */
    private function buildDirective(Node $node, $parent)
    {
        // TODO : implement
    }
}
