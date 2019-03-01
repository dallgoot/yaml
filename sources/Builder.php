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
    /** @var bool */
    public static $dateAsObject = false;

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
        if ($_debug === 2) {
            print_r($root);
            return;
        }
        self::$_debug = $_debug;
        $documents = [];
        $buffer = new NodeList();
        try {
            foreach ($root->value as $child) {
                if ($child instanceof NodeDocEnd && $child !== $root->value->top()) {
                    self::pushAndSave($child, $buffer, $documents);
                } elseif ($child instanceof NodeDocStart && $buffer->count() > 0 && $buffer->hasContent()) {
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
     *  Builds the tree of Node for this document (as NodeList)
     *
     * @param NodeList $list   the list of nodes that constitutes the current document
     * @param int      $docNum the index (starts @ 0) of this document in the whole YAML content provided to self::buildContent
     *
     * @return YamlObject the YAML document as an object
     */
    private static function buildDocument(NodeList &$list, int $docNum):YamlObject
    {
        $yamlObject = new YamlObject;
        $rootNode   = new NodeRoot();
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

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param string $v a string value
     *
     * @return mixed The value with appropriate PHP type
     * @throws \Exception if happens in Regex::isDate or Regex::isNumber
     */
    public static function getScalar(string $v, bool $onlyScalar = false)
    {
        if (Regex::isDate($v))   return self::$dateAsObject && !$onlyScalar ? date_create($v) : $v;
        if (Regex::isNumber($v)) return self::getNumber($v);
        $types = ['yes'   => true,
                  'no'    => false,
                  'true'  => true,
                  'false' => false,
                  'null'  => null,
                  '.inf'  => \INF,
                  '-.inf' => -\INF,
                  '.nan'  => \NAN
        ];
        return array_key_exists(strtolower($v), $types) ? $types[strtolower($v)] : $v;
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param string $v a string value
     *
     * @return int|float   The scalar value with appropriate PHP type
     * @todo make sure there 's only ONE dot before cosndering a float
     */
    private static function getNumber(string $v)
    {
        if (preg_match(Regex::OCTAL_NUM, $v)) return intval(base_convert($v, 8, 10));
        if (preg_match(Regex::HEX_NUM, $v))   return intval(base_convert($v, 16, 10));
        return is_bool(strpos($v, '.')) ? intval($v) : floatval($v);
    }

    private static function pushAndSave(Node $child, NodeList $buffer, array &$documents)
    {
        $buffer->push($child);
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList();
    }

    private static function saveAndPush(Node $child, NodeList $buffer, array &$documents)
    {
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList($child);
    }


}
