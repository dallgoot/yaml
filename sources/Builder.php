<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\DocEnd;
use Dallgoot\Yaml\Nodes\DocStart;

/**
 * Constructs the result (YamlObject or array) according to every Nodes and their values
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
final class Builder
{
    /** @var boolean */
    public static $dateAsObject = false;

    private static $_debug;

    const INVALID_DOCUMENT = "DOCUMENT %d is invalid,";

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param Root $root  The NodeRoot node
     * @param int  $_debug    the level of debugging requested
     *
     * @return array|YamlObject|null   list of documents or just one.
     */
    public static function buildContent(Root $root, int $_debug = 0)
    {
        if ($_debug === 2) {
            print_r($root);
            return null;
        }
        self::$_debug = $_debug;
        $documents = [];
        $buffer = new NodeList();
        try {
            foreach ($root->value as $child) {
                if ($child instanceof DocEnd && $child !== $root->value->top()) {
                    self::pushAndSave($child, $buffer, $documents);
                } elseif ($child instanceof DocStart && $buffer->count() > 0 && $buffer->hasContent()) {
                    self::saveAndPush($child, $buffer, $documents);
                } else {
                    $buffer->push($child);
                }
            }
            $documents[] = self::buildDocument($buffer, count($documents) +1);
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception($e->getMessage(), 1, $e);
        }
        return count($documents) === 1 ? $documents[0] : $documents;
    }

    /**
     *  Builds the tree of Node (NodeList) for this document
     *
     * @param NodeList $list   the list of nodes that constitutes the current document
     * @param int      $docNum the index (starts @ 0) of this document in the whole YAML content provided to self::buildContent
     *
     * @return YamlObject the YAML document as an object
     */
    public static function buildDocument(NodeList &$list, int $docNum):YamlObject
    {
        $yamlObject = new YamlObject;
        $rootNode   = new Root();
        $list->setIteratorMode(NodeList::IT_MODE_DELETE);
        try {
            foreach ($list as $child) {
                $rootNode->add($child);
            }
            if (self::$_debug === 3) {
                echo "Document #$docNum\n";
                print_r($rootNode);
            }
            return $rootNode->build($yamlObject);
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum).':'.$e->getMessage(), 2, $e);
        }
    }

    public static function pushAndSave(NodeGeneric $child, NodeList &$buffer, array &$documents)
    {
        $buffer->push($child);
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList();
    }

    public static function saveAndPush(NodeGeneric $child, NodeList &$buffer, array &$documents)
    {
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList($child);
    }


}
