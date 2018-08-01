<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;

/**
 * Constructs the result (YamlObject or array) according to every Node and respecting value
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 */
final class Builder
{
    private static $_root;
    private static $_debug;

    const ERROR_NO_KEYNAME = self::class.": key has NO IDENTIFIER on line %d";
    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";


    private static function build(object $node, &$parent = null)
    {
        if ($node instanceof NodeList) return self::buildNodeList($node, $parent);
        return self::buildNode($node, $parent);
    }

    private static function buildNodeList(NodeList $node, &$parent)
    {
        $type = property_exists($node, "type") ? $node->type : null;
        if ($type&(Y\RAW|Y\LITTERALS)) {
            return self::litteral($node, $type);
        }
        $p = $parent;
        switch ($type) {
            case Y\MAPPING: //fall through
            case Y\SET:      $p = new \StdClass; break;
            case Y\SEQUENCE: $p = []; break;
            // case Y\KEY: $p = $parent;break;
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

    private static function buildNode(Node $node, &$parent)
    {
        list($line, $type, $value, $identifier) = [$node->line, $node->type, $node->value, $node->identifier];
        switch ($type) {
            case Y\COMMENT: self::$_root->addComment($line, $value); return;
            case Y\DIRECTIVE: return; //TODO
            case Y\ITEM: self::buildItem($value, $parent); return;
            case Y\KEY:  self::buildKey($node, $parent); return;
            case Y\REF_DEF: //fall through
            case Y\REF_CALL://TODO: self::build returns what ?
                $tmp = is_object($value) ? self::build($value, $parent) : $node->getPhpValue();
                if ($type === Y\REF_DEF) self::$_root->addReference($identifier, $tmp);
                return self::$_root->getReference($identifier);
            case Y\SET_KEY:
                $key = json_encode(self::build($value, $parent), JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
                if (empty($key))
                    throw new \Exception("Cant serialize complex key: ".var_export($value, true), 1);
                $parent->{$key} = null;
                return;
            case Y\SET_VALUE:
                $prop = array_keys(get_object_vars($parent));
                $key = end($prop);
                if (property_exists($value, "type") && ($value->type & (Y\ITEM|Y\MAPPING))) {
                    $p = $value->type === Y\ITEM ? [] : new \StdClass;
                    self::build($value, $p);
                } else {
                    $p = self::build($value, $parent->{$key});
                }
                $parent->{$key} = $p;
                return;
            case Y\TAG:
                if ($parent === self::$_root) {
                    $parent->addTag($identifier); return;
                } else {//TODO: have somewhere a list of common tags and their treatment
                    if (in_array($identifier, ['!binary', '!str'])) {
                        if ($value->value instanceof NodeList) $value->value->type = Y\RAW;
                        else $value->type = Y\RAW;
                    }
                    $val = is_null($value) ? null : self::build(/** @scrutinizer ignore-type */ $value, $node);
                    return new Tag($identifier, $val);
                }
            default:
                return is_object($value) ? self::build($value, $parent) : $node->getPhpValue();
        }
    }

    /**
     * Builds a key and set the property + value to the parent given
     *
     * @param Node $node       The node
     * @param object|array $parent       The parent
     *
     * @throws \ParseError if Key has no name(identifier)
     * @return null
     */
    private static function buildKey($node, &$parent):void
    {
        list($identifier, $value) = [$node->identifier, $node->value];
        if (is_null($identifier)) {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $node->line));
        } else {
            if ($value instanceof Node && ($value->type & (Y\KEY|Y\ITEM))) {
                $parent->{$identifier} = $value->type & Y\KEY ? new \StdClass : [];
                self::build($value, $parent->{$identifier});
            } elseif (is_object($value)) {
                $parent->{$identifier} = self::build(/** @scrutinizer ignore-type */ $value, $parent->{$identifier});
            } else {
                $parent->{$identifier} = $node->getPhpValue();
            }
        }
    }

    private static function buildItem($value, &$parent):void
    {
        if (!is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an Iterable not ".(is_object($parent) ? get_class($parent) : gettype($parent)), 1);
        }
        if ($value instanceof Node && $value->type === Y\KEY) {
            $parent[$value->identifier] = self::build($value->value, $parent[$value->identifier]);
        } else {
            $index = count($parent);
            $parent[$index] = self::build($value, $parent[$index]);
        }
    }

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param   Node   $_root      The root node
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
            return [self::buildDocument($q, 0)];
        }
        $_root->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        foreach ($_root->value as $child) {
            if ($child->type & Y\DOC_START) $totalDocStart++;
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0;
            if (!isset($documents[$currentDoc])) $documents[$currentDoc] = new NodeList();
            $documents[$currentDoc]->push($child);
        }
        // $_debug >= 2 && var_dump($documents);//var_dump($documents);die("documents");
        $content = array_map([self::class, 'buildDocument'], $documents, array_keys($documents));
        return count($content) === 1 ? $content[0] : $content;
    }

    private static function buildDocument(NodeList $list, int $key):YamlObject
    {
        self::$_root = new YamlObject();
        $childTypes = self::getChildrenTypes($list);
        $isMapping  = count(array_intersect($childTypes, [Y\KEY, Y\MAPPING])) > 0;
        $isSequence = in_array(Y\ITEM, $childTypes);
        $isSet      = in_array(Y\SET_VALUE, $childTypes);
        if ($isMapping && $isSequence) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $key));
        } else {
            switch (true) {
                case $isSequence: $list->type = Y\SEQUENCE;break;
                case $isSet:      $list->type = Y\SET;break;
                default:          $list->type = Y\MAPPING;
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

    private static function litteral(NodeList $children, $type):string
    {
        $children->rewind();
        $refIndent = $children->current()->indent;
        $separator = $type === Y\RAW ? '' : "\n";
        $action = function ($c) { return $c->value; };
        if ($type & Y\LITT_FOLDED) {
            $separator = ' ';
            $action = function ($c) use ($refIndent) {
                return $c->indent > $refIndent || ($c->type & Y\BLANK) ? "\n".$c->value : $c->value;
            };
        }
        $tmp = [];
        $children->rewind();
        foreach ($children as $child) {
            $tmp[] = $action($child);
        }
        return implode($separator, $tmp);
    }

    private static function getChildrenTypes(NodeList $children):array
    {
        $types = [];
        foreach ($children as $child) {
            $types[] = $child->type;
        }
        return array_unique($types);
    }
}
