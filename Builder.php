<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Node as Node, Types as T, YamlObject, Tag, Builder};
use \SplDoublyLinkedList as DLL;

/**
 *
 */
class Builder
{
    private static $root;
    private static $debug;

    const ERROR_NO_KEYNAME = self::class.": key has NO NAME on line %d";
    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";


    private static function build(object $node, &$parent = null)
    {
        if ($node instanceof DLL) return self::buildDLL($node, $parent);
        return self::buildNode($node, $parent);
    }

    private static function buildDLL(DLL $node, &$parent)
    {
        $type = property_exists($node, "type") ? $node->type : null;
        if (is_object($parent) && $parent instanceof YamlObject) {
            $p = $parent;
        } else {
            switch ($type) {
                case T::MAPPING: //fall through
                case T::SET:  $p = new \StdClass;break;
                case T::SEQUENCE: $p = [];break;
                case T::KEY: $p = $parent;break;
            }
        }
        if (in_array($type, [T::RAW, T::LITTERAL, T::LITTERAL_FOLDED])) {
            return self::litteral($node, $type);
        }
        foreach ($node as $child) {
            $result = self::build($child, $p);
            if (!is_null($result)) {
                if (is_string($result)) {
                    if ($p instanceof YamlObject) {
                        $p->setText($result);
                    } else {
                        $p .= ' '.$result;
                    }
                } else {
                    return $result;
                }
            }
        }
        return is_string($p) ? trim($p) : $p;
    }

    private static function buildNode(Node $node, &$parent)
    {
        list($line, $type, $value) = [$node->line, $node->type, $node->value];
        $name  = property_exists($node, "name") ? $node->name : null;
        switch ($type) {
            case T::COMMENT: self::$root->addComment($line, $value);return;
            case T::DIRECTIVE: return;//TODO
            case T::ITEM: self::buildItem($value, $parent);return;
            case T::KEY:  self::buildKey($node, $parent);return;
            case T::REF_DEF: //fall through
            case T::REF_CALL:
                $tmp = is_object($value) ? self::build($value, $parent) : $node->getPhpValue();
                if ($type === T::REF_DEF) self::$root->addReference($name, $tmp);
                return self::$root->getReference($name);
            case T::SET_KEY:
                $key = json_encode(self::build($value, $parent), JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
                if (empty($key))
                    throw new \Exception("Cant serialize complex key: ".var_export($value, true), 1);
                $parent->{$key} = null;
                return;
            case T::SET_VALUE:
                $prop = array_keys(get_object_vars($parent));
                $key = end($prop);
                if (property_exists($value, "type") && in_array($value->type, [T::ITEM, T::MAPPING])) {
                    $p = $value->type === T::ITEM  ? [] : new \StdClass;
                    self::build($value, $p);
                } else {
                    $p = self::build($value, $parent->{$key});
                }
                $parent->{$key} = $p;
                return;
            case T::TAG:
                if ($parent === self::$root) {
                    $parent->addTag($name);return;
                } else {
                    if (in_array($name, ['!binary', '!str'])) {
                        if (is_object($value->value)) $value->value->type = T::RAW;
                        else $value->type = T::RAW;
                    }
                    $val = is_null($value) ? null : self::build($value, $node);
                    return new Tag($name, $val);
                }
            default:
                return is_object($value) ? self::build($value, $parent) : $node->getPhpValue();
        }
    }

    /**
     * Builds a key and set the property + value to the parent given
     *
     * @param      Node   $node    The node
     * @param      object|array  $parent  The parent
     * @throws \ParseError if Key has no name
     */
    private static function buildKey($node, &$parent)
    {
        list($name, $value) = [$node->name, $node->value];
        if (is_null($name)) {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $node->line));
        } else {
            if ($value instanceof Node && in_array($value->type, [T::KEY, T::ITEM])) {
                $parent->{$name} = $value->type === T::KEY ? new \StdClass : [];
                self::build($value, $parent->{$name});
            } elseif (is_object($value)) {
                $parent->{$name} = self::build($value, $parent->{$name});
            } else {
                $parent->{$name} = $node->getPhpValue();
            }
        }
    }

    private static function buildItem($value, &$parent):void
    {
        if ($value instanceof Node && $value->type === T::KEY) {
            $parent[$value->name] = self::build($value->value, $parent[$value->name]);
        } else {
            $index = count($parent);
            $parent[$index] = self::build($value, $parent[$index]);
        }
    }

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param      Node   $root   The root node
     * @return     array|YamlObject  list of documents or juste one.
     */
    public static function buildContent(Node $root, int $debug)
    {
        self::$debug = $debug;
        $totalDocStart = 0;
        $documents = [];
        if ($root->value instanceof Node) {
            $q = new DLL;
            $q->push($root->value);
            return [self::buildDocument($q, 0)];
        }
        $root->value->setIteratorMode(DLL::IT_MODE_DELETE);
        foreach ($root->value as $child) {
            if ($child->type === T::DOC_START) $totalDocStart++;
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0;
            if (!isset($documents[$currentDoc])) $documents[$currentDoc] = new DLL();
            $documents[$currentDoc]->push($child);
        }
        $debug >= 2 && var_dump($documents);
        $content = array_map([self::class, 'buildDocument'], $documents, array_keys($documents));
        return count($content) === 1 ? $content[0] : $content;
    }

    private static function buildDocument(DLL $list, int $key):YamlObject
    {
        self::$root = new YamlObject();
        $childTypes = self::getChildrenTypes($list);
        $isMapping  = count(array_intersect($childTypes, [T::KEY, T::MAPPING])) > 0;
        $isSequence = in_array(T::ITEM, $childTypes);
        $isSet      = in_array(T::SET_VALUE, $childTypes);
        if ($isMapping && $isSequence) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $key));
        } else {
            switch (true) {
                case $isSequence: $list->type = T::SEQUENCE;break;
                case $isSet: $list->type = T::SET;break;
                case $isMapping://fall through
                default:$list->type = T::MAPPING;
            }
        }
        self::$debug >= 3 && var_dump(self::$root, $list);
        return self::build($list, self::$root);
    }

    private static function litteral(DLL $children, $type):string
    {
        $children->rewind();
        $refIndent = $children->current()->indent;
        $separator = $type === T::RAW ? '' : "\n";
        $action = function ($c) { return $c->value; };
        if ($type === T::LITTERAL_FOLDED) {
            $separator = ' ';
            $action = function ($c) use ($refIndent) {
                return $c->indent > $refIndent || $c->type === T::EMPTY ? "\n".$c->value : $c->value;
            };
        }
        $tmp = [];
        $children->rewind();
        foreach ($children as $child) {
            $tmp[] = $action($child);
        }
        return implode($separator, $tmp);
    }

    private static function getChildrenTypes(DLL $children):array
    {
        $types = [];
        foreach ($children as $child) {
            $types[] = $child->type;
        }
        return array_unique($types);
    }
}
