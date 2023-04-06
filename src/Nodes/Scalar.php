<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Tag\TagFactory;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\Loader;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Scalar extends NodeGeneric
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $value = trim($nodeString);
        if ($value !== '') {
            $hasComment = strpos($value, ' #');
            if (!is_bool($hasComment)) {
                $realValue    = trim(substr($value, 0, $hasComment));
                $commentValue = trim(substr($value, $hasComment));
                $realNode = NodeFactory::get($realValue, $line);
                $realNode->indent = null;
                $commentNode = NodeFactory::get($commentValue, $line);
                $commentNode->indent = null;
                $this->add($realNode);
                $this->add($commentNode);
            }
        }
    }

    public function build(&$parent = null)
    {
        if (!is_null($this->tag)) {
            $tagged = TagFactory::transform($this->tag, $this);
            if ($tagged instanceof NodeGeneric || $tagged instanceof NodeList) {
                return $tagged->build();
            }
            return $tagged;
        }
        return is_null($this->value) ? $this->getScalar(trim($this->raw)) : $this->value->build();
    }

    public function getTargetOnLessIndent(NodeGeneric &$node): NodeGeneric
    {
        if ($node instanceof Scalar || $node instanceof Blank) {
            return $this->getParent();
        } else {
            return $this->getParent($node->indent);
        }
    }

    public function getTargetOnMoreIndent(NodeGeneric &$node): NodeGeneric
    {
        return $this->getParent();
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
    public function getScalar(string $v, bool $onlyScalar = false)
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
        if (Regex::isDate($v))   return ($this->getRoot()->getYamlObject()->getOptions() & Loader::NO_OBJECT_FOR_DATE) && !$onlyScalar ? date_create($v) : $v;
        if (Regex::isNumber($v)) return $this->getNumber($v);
        $types = [
            'yes'   => true,
            'no'    => false,
            'true'  => true,
            'false' => false,
            'null'  => null,
            '.inf'  => \INF,
            '-.inf' => -\INF,
            '.nan'  => \NAN
        ];
        return array_key_exists(strtolower($v), $types) ? $types[strtolower($v)] : $this->replaceSequences($v);
    }

    public function replaceSequences(string $value = ''): string
    {
        $replaceUnicodeSeq = function ($matches) {
            return json_decode('"' . $matches[1] . '"');
        };
        $replaceNonPrintable = function ($matches) {
            return $matches[1] . "";
        };
        return preg_replace_callback_array(
            [
                '/((?<![\\\\])\\\\x[0-9a-f]{2})/i' => $replaceUnicodeSeq,
                '/((?<![\\\\])\\\\u[0-9a-f]{4,})/i' => $replaceUnicodeSeq,
                '/(\\\\b|\\\\n|\\\\t|\\\\r)/' => $replaceNonPrintable
            ],
            $value
        );
    }


    /**
     * Returns the correct PHP type according to the string value
     *
     * @return int|float   The scalar value with appropriate PHP type
     * @todo or scientific notation matching the regular expression -? [1-9] ( \. [0-9]* [1-9] )? ( e [-+] [1-9] [0-9]* )?
     */
    private function getNumber(string $v)
    {
        if ((bool) preg_match(Regex::OCTAL_NUM, $v)) return intval(base_convert($v, 8, 10));
        if ((bool) preg_match(Regex::HEX_NUM, $v))   return intval(base_convert($v, 16, 10));
        return is_bool(strpos($v, '.')) || substr_count($v, '.') > 1 ? intval($v) : floatval($v);
    }
}
