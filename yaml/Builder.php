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
        if ($_root->value instanceof Node) {
            $q = new NodeList;
            $q->push($_root->value);
            // return self::buildNodeList($q, new YamlObject);
            self::$_root = new YamlObject;
            $tmp =  self::buildNodeList($q, self::$_root);
            // var_dump('alone', $tmp);
            return $tmp;
        }
        $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        foreach ($_root->value as $child) {
            if ($child->type & Y::DOC_START) $totalDocStart++;
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0;
            if (!isset($documents[$currentDoc])) $documents[$currentDoc] = new NodeList();
            $documents[$currentDoc]->push($child);
        }
        // $content = array_map([self::class, 'buildDocument'], $documents, array_keys($documents));
        $content = [];
        foreach ($documents as $num => $list) {
            try {
                self::$_root = new YamlObject;
                // $tmp = var_dump('insideforeach'.$tmp);
                $content[] = self::buildNodeList($list, self::$_root);
            } catch (\Exception $e) {
                throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $num));
            }
        }
        // $content = array_map([self::class, 'buildNodeList'], $documents, array_keys($documents));
        return count($content) === 1 ? $content[0] : $content;
    }

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
    private static function buildNodeList(NodeList $node, &$parent=null)
    {
        if (is_null($node->type)) {
            $childTypes  = $node->getTypes();
            if ($childTypes & (Y::KEY|Y::SET_KEY)) {
                if ($childTypes & Y::ITEM) {
                    // TODO: replace the document index in HERE ----------v
                    throw new \ParseError(sprintf(self::INVALID_DOCUMENT, 0));
                } else {
                    $node->type = Y::MAPPING;
                }
            } else {
                if ($childTypes & Y::ITEM) {
                    $node->type = Y::SEQUENCE;
                } elseif (!($childTypes & Y::COMMENT)) {
                    $node->type = Y::LITT_FOLDED;
                }
            }
        }
        // var_dump('nodetype:'.Y::getName($node->type) );
        if ($node->type & (Y::RAW | Y::LITTERALS)) {
            return self::buildLitteral($node, $node->type);
        }
        if ($node->type & (Y::COMPACT_MAPPING|Y::MAPPING|Y::SET)) {
            $out = $parent ?? new \StdClass;//var_dump("PAS LA");
            foreach ($node as $key => $child) {
                if ($child->type & (Y::KEY)) {
                    self::buildKey($child, $out);
                } else {
                    self::build($child, $out);
                }
            }
            if ($node->type & Y::COMPACT_MAPPING) {
                $out = new Compact($out);
            }
        } elseif ($node->type & (Y::COMPACT_SEQUENCE|Y::SEQUENCE)) {
            $out = $parent ?? [];//var_dump("HERE");
            foreach ($node as $key => $child) {
                if ($child->type & Y::ITEM) {
                    self::buildItem($child, $out);
                } else {
                    self::build($child);
                }
            }
            if ($node->type & Y::COMPACT_SEQUENCE) {
                $out = new Compact($out);
            }
        } else {
            $tmpString = null;//var_dump("PAS ICI");
            foreach ($node as $key => $child) {
                 if ($child->type & (Y::SCALAR|Y::QUOTED)) {
                    if ($parent) {
                        $parent->setText(self::build($child, $parent));
                    } else {
                        $tmpString .= self::build($child, $parent);
                    }
                } else {
                    self::build($child, $parent);
                }
            }
            $out = is_null($tmpString) ? $parent : $tmpString;
        }
        return $out;
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
        if ($type & (Y::COMPACT_MAPPING|Y::COMPACT_SEQUENCE)) {
            return self::buildNodeList($node->value, $parent);
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
    private static function buildKey(Node $node, &$parent=null)
    {
        extract((array) $node, EXTR_REFS);
        if (is_null($identifier)) {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $line));
        } else {
            if ($value instanceof Node) {
                if ($value->type & (Y::ITEM|Y::KEY)) {
                    $list = new NodeList();
                    $list->push($value);
                    $list->type = $value->type & Y::ITEM ? Y::SEQUENCE : Y::MAPPING;
                    $value = $list;
                } else {
                    $result = self::build($value);
                }
            }
            if ($value instanceof NodeList) {
                $childTypes = $value->getTypes();
                if (is_null($value->type) && $childTypes & Y::SCALAR && !($childTypes & Y::COMMENT)) {
                    $result = self::buildLitteral($value, Y::LITT_FOLDED);
                } else {
                    $result = self::buildNodeList($value);
                }
            }
            if (is_null($parent)) {
                return $result;
            } else {
                if (is_array($parent)) {
                    $parent[$identifier] = $result;
                } else {
                    $parent->{$identifier} = $result;
                }
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
        extract((array) $node, EXTR_REFS);//var_dump(__METHOD__);
        if (!is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an Iterable not ".(is_object($parent) ? get_class($parent) : gettype($parent)), 1);
        }
        $ref = $parent instanceof \ArrayIterator ? $parent->getArrayCopy() : $parent;
        $numKeys = array_filter(array_keys($ref), 'is_int');
        $key = count($numKeys) > 0 ? max($numKeys) + 1 : 0;
        if ($value instanceof Node) {
            if($value->type & Y::KEY) {
                self::buildKey($node->value, $parent);
                return;
            } elseif ($value->type & Y::ITEM) {
                $list = new NodeList();
                $list->push($value);
                $list->type = Y::SEQUENCE;
                $result = self::buildNodeList($list);
            } else {
                $result = self::build($value);
            }
        } elseif ($value instanceof NodeList) {
            $result = self::buildNodeList($value);
        }
        $parent[$key] = $result;
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
                $val = $child->value;
                while (is_object($val)) {
                    $val = $val->value;
                }
                $lines[] = $prefix.$val;
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
        $built = is_object($node->value) ? self::build($node->value) : null;
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
    private static function buildTag(Node $node, &$parent)
    {
        if ($parent === self::$_root && empty($node->value)) {
            $parent->addTag((string) $node->identifier);
            return;
        }
        $target = $node->value;
        if ($node->value instanceof Node) {
            if ($node->value->type & (Y::KEY|Y::ITEM)) {
                if (is_null($parent)) {
                    $target = new NodeList;
                    $target->push($node->value);
                    $target->type = $node->value->type & Y::KEY ? Y::MAPPING : Y::SEQUENCE;
                } else {
                    $node->value->type & Y::KEY ? self::buildKey($node->value, $parent) : self::buildItem($node->value, $parent);
                }
            }
        }
        //TODO: have somewhere a list of common tags and their treatment
        // if (in_array($node->identifier, ['!binary', '!str'])) {
        //     $target->type = Y::RAW;
        // }

        return new Tag($node->identifier, is_object($target) ? self::build($target) : null);
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
