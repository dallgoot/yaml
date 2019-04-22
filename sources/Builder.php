<?php

namespace Dallgoot\Yaml;

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
     * @param NodeRoot $root  The NodeRoot node
     * @param int  $_debug    the level of debugging requested
     *
     * @return array|YamlObject   list of documents or just one.
     */
    public static function buildContent(NodeRoot $root, int $_debug = 0)
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
     * @throws \Exception if it happens in Regex::isDate or Regex::isNumber
     * @todo implement date as DateTime Object
     */
    public static function getScalar(string $v, bool $onlyScalar = false)
    {
        /*
         10.3.2. Tag Resolution

The core schema tag resolution is an extension of the JSON schema tag resolution.

All nodes with the “!” non-specific tag are resolved, by the standard convention, to “tag:yaml.org,2002:seq”, “tag:yaml.org,2002:map”, or “tag:yaml.org,2002:str”, according to their kind.

Collections with the “?” non-specific tag (that is, untagged collections) are resolved to “tag:yaml.org,2002:seq” or “tag:yaml.org,2002:map” according to their kind.

Scalars with the “?” non-specific tag (that is, plain scalars) are matched with an extended list of regular expressions. However, in this case, if none of the regular expressions matches, the scalar is resolved to tag:yaml.org,2002:str (that is, considered to be a string).
 Regular expression       Resolved to tag
 null | Null | NULL | ~      tag:yaml.org,2002:null
 Empty      tag:yaml.org,2002:null
 true | True | TRUE | false | False | FALSE      tag:yaml.org,2002:bool
 [-+]? [0-9]+    tag:yaml.org,2002:int (Base 10)
 0o [0-7]+   tag:yaml.org,2002:int (Base 8)
 0x [0-9a-fA-F]+     tag:yaml.org,2002:int (Base 16)
 [-+]? ( \. [0-9]+ | [0-9]+ ( \. [0-9]* )? ) ( [eE] [-+]? [0-9]+ )?      tag:yaml.org,2002:float (Number)
 [-+]? ( \.inf | \.Inf | \.INF )     tag:yaml.org,2002:float (Infinity)
 \.nan | \.NaN | \.NAN   tag:yaml.org,2002:float (Not a number)
 *   tag:yaml.org,2002:str (Default)
 */
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
     * @todo or scientific notation matching the regular expression -? [1-9] ( \. [0-9]* [1-9] )? ( e [-+] [1-9] [0-9]* )?
     */
    private static function getNumber(string $v)
    {
        if ((bool) preg_match(Regex::OCTAL_NUM, $v)) return intval(base_convert($v, 8, 10));
        if ((bool) preg_match(Regex::HEX_NUM, $v))   return intval(base_convert($v, 16, 10));
        return is_bool(strpos($v, '.')) || substr_count($v, '.') > 1 ? intval($v) : floatval($v);
    }

    public static function pushAndSave(Node $child, NodeList &$buffer, array &$documents)
    {
        $buffer->push($child);
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList();
    }

    public static function saveAndPush(Node $child, NodeList &$buffer, array &$documents)
    {
        $documents[] = self::buildDocument($buffer, count($documents) + 1);
        $buffer = new NodeList($child);
    }


}
