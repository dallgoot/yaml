<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes\Root;
use Dallgoot\Yaml\Nodes\DocEnd;
use Dallgoot\Yaml\Nodes\DocStart;
use Dallgoot\Yaml\Types\YamlObject;

/**
 * Constructs the result (YamlObject or array) according to every Nodes and their values
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Builder
{
    public bool $dateAsObject = false;

    private int $_options;
    private int $_debug = 0;

    const INVALID_DOCUMENT = "DOCUMENT %d is invalid,";

    public function __construct($options, $debug)
    {
        $this->_options = $options;
        $this->_debug = $debug;
    }
    /**
     * Builds a YAM content.  check multiple documents & split if more than one documents
     *
     * @param Root $root  The NodeRoot node
     *
     * @return array|YamlObject|null   list of documents or just one, null if appropriate debug lvl
     */
    public function buildContent(Root $root)
    {
        switch ($this->_debug) {
            case 2 : print_r($root);
            case 1 : return null;
        }
        $documents = [];
        $buffer = new NodeList();
        try {
            foreach ($root->value as $child) {
                if ($child instanceof DocEnd && $child !== $root->value->top()) {
                    $this->pushAndSave($child, $buffer, $documents);
                } elseif ($child instanceof DocStart) {
                    $this->saveAndPush($child, $buffer, $documents);
                } else {
                    $buffer->push($child);
                }
            }
            $documents[] = $this->buildDocument($buffer, count($documents) + 1);
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), 1, $e);
        }
        return count($documents) === 1 ? $documents[0] : $documents;
    }

    /**
     *  Builds the tree of Node (NodeList) for this document
     *
     * @param NodeList $list   the list of nodes that constitutes the current document
     * @param int      $docNum the index (starts @ 0) of this document in the whole YAML content provided to $this->buildContent
     *
     * @return YamlObject the YAML document as an object
     */
    public function buildDocument(NodeList &$list, int $docNum): YamlObject
    {
        $yamlObject = new YamlObject($this->_options);
        $rootNode   = new Root();
        $list->setIteratorMode(NodeList::IT_MODE_DELETE);
        try {
            foreach ($list as $child) {
                $rootNode->add($child);
            }
            if ($this->_debug === 3) {
                echo "Document #$docNum\n";
                print_r($rootNode);
            }
            return $rootNode->build($yamlObject);
        } catch (\Throwable $e) {
            throw new \ParseError(sprintf(self::INVALID_DOCUMENT, $docNum) . ':' . $e->getMessage(), 2, $e);
        }
    }

    public function pushAndSave(DocEnd $child, NodeList &$buffer, array &$documents)
    {
        $buffer->push($child);
        $documents[] = $this->buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList();
    }

    public function saveAndPush(DocStart $child, NodeList &$buffer, array &$documents)
    {
        if ($buffer->count() > 0 && $buffer->hasContent()) {
            $documents[] = $this->buildDocument($buffer, count($documents) + 1);
            $buffer = new NodeList($child);
        } else {
            $buffer->push($child);
        }
    }
}
