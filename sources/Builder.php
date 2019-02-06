<?php

namespace Dallgoot\Yaml;

/**
 * Constructs the result (YamlObject or array) according to every Node and respecting value
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Builder
{
    public  static $_root;
    private static $_debug;

    const INVALID_DOCUMENT = "DOCUMENT %d is invalid,";

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param NodeRoot $root  The root node : Node with Node->type === YAML::ROOT
     * @param int  $_debug the level of debugging requested
     *
     * @return array|YamlObject      list of documents or just one.
     */
    public static function buildContent(NodeRoot $root, int $_debug)
    {
        self::$_debug = $_debug;
        $documents = [];
        $buffer = new NodeList();
        foreach ($root->getValue() as $child) {
            if ($child instanceof NodeDocEnd && $child !== $root->value->top()) {
                $buffer->push($child);
                $documents[] = self::buildDocument($buffer, count($documents));
                $buffer = new NodeList();
                continue;
            } elseif ($child instanceof NodeDocStart && $buffer->count() > 0 && $buffer->hasContent()) {
                $documents[] = self::buildDocument($buffer, count($documents));
                $buffer = new NodeList($child);
                continue;
            }
            $buffer->push($child);
        }
        try {
            $documents[] = self::buildDocument($buffer, count($documents));
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception($e->getMessage(), 1, $e);
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
    {
        self::$_root = new YamlObject;
        try {
            $out = self::buildNodeList($list, self::$_root);
            if (is_string($out)) {
                $out = self::$_root->setText($out);
            }
        } catch (\Exception|\Error $e) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum).':'.$e->getMessage(), 2, $e);
        }
        return $out;
    }


    /**
     * Builds a node list.
     *
     * @param NodeList $list   The node
     * @param mixed    $parent The parent
     *
     * @return mixed The parent (object|array) or a string representing the NodeList.
     */
    public static function buildNodeList(NodeList $list, &$parent = null)
    {
        $out = '';
        foreach ($list as $child) {
            if ($child instanceof NodeDocStart) {
                $child->build($parent);
            } else {
                $out .= ','.$child->build($parent);
            }
        }
        if (is_string($out)) {
            $result = implode(explode(',', $out));
            $out = $result === '' ? null : Node::getScalar($result);
        }
        return is_null($out) ? $parent : $out;
    }

}
