<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

class Node
{
    public $indent = -1;
    public $line;
    public $type;
    public $value;//can be Scalar, Node or SplQueue
    private $_parent;

    private const yamlNull  = "null";
    private const yamlFalse = "false";
    private const yamlTrue  = "true";
    private const yamlAN = "[\w ]+";
    private const yamlNum = "-?[\d.e]+";
    private const yamlSimpleValue = "(?P<sv>".self::yamlNull."|".
                                    self::yamlFalse."|".
                                    self::yamlTrue."|".
                                    self::yamlAN."|".
                                    self::yamlNum.")";
    private const sequenceForMap = "(?P<seq>\[(?:(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+\])";
    private const yamlMapping  = "(?P<map>{\s*(?:".self::yamlAN."\s*:\s*(?:".self::yamlSimpleValue."|".self::sequenceForMap."|(?P>map)),?\s*)+})";
    private const mapForSequence = "(?P<map>{\s*(?:".self::yamlAN."\s*:\s*(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+})";
    private const yamlSequence = "(?P<seq>\[(?:(?:".self::yamlSimpleValue."|".self::mapForSequence."|(?P>seq)),?\s*)+\])";

    public function __construct($nodeString = null, $line = null)
    {
        // echo self::yamlSequence;exit();
        $this->line = $line;
        if (is_null($nodeString)) {
            $this->type = T::ROOT;
        } else {
            $this->parse($nodeString);
        }
    }
    public function setParent(Node $node)
    {
        $this->_parent = $node;
        return $this;
    }

    public function getParent($indent = null):Node
    {
        if (is_null($indent)) {
             return $this->_parent ?? $this;
        }
        $cursor = $this;
        while ($cursor->indent >= $indent) {
            $cursor = $cursor->_parent;
        }
        return $cursor;
    }

    public function add(Node $child)
    {
        $child->setParent($this);
        $current = $this->value;
        if (is_null($current)) {
            $this->value = $child;
            return;
        } elseif ($current instanceof Node) {
            if ($current->type === T::EMPTY) {
                $this->value = $child;
                return;
            } else {
                $this->value = new \SplQueue();
                $this->value->enqueue($current);
                $this->value->enqueue($child);
            }
        } elseif ($current instanceof \SplQueue) {
            $this->value->enqueue($child);
        }
        //modify type according to child
        if ($this->value instanceof \SplQueue && !property_exists($this->value, "type")) {
            switch ($child->type) {
                case T::KEY:    $this->value->type = T::MAPPING;break;
                case T::ITEM:   $this->value->type = T::SEQUENCE;break;
                case T::STRING: $this->value->type = $this->type;break;
            }
        }
    }

    public function getDeepestNode():Node
    {
        $cursor = $this;
        while ($cursor->value instanceof Node) {
            $cursor = $cursor->value;
        }
        return $cursor;
    }
    /**
    *  CAUTION : the types assumed here are NOT FINAL : they CAN be adjusted according to parent
    */
    private function parse(String $nodeString):Node
    {
        //permissive to tabs but replacement before processing
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);
        $this->indent = strspn($nodeValue, ' ');
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = T::EMPTY;
            $this->indent = 0;
        } elseif (substr($nodeValue, 0, 3) === '...') {//TODO: can have something after?
            $this->type = T::DOC_END;
        } elseif (preg_match('/^([[:alpha:]][[:alpha:]-_ ]*[\s\t]*):([\s\t].*)?/', $nodeValue, $matches)) {
            $this->type = T::KEY;
            $this->name = trim($matches[1]);
            if (isset($matches[2]) && !empty(trim($matches[2]))) {
                $n = new Node(trim($matches[2]), $this->line);
            } else {
                $n = new Node();
                $n->type = T::EMPTY;
            }
            $n->indent = $this->indent + strlen($this->name);
            $n->setParent($this);
            $this->value = $n;
        } else {//NOTE: can be of another type according to parent
            list($this->type, $this->value) = $this->_define($nodeValue);
        }
        return $this;
    }

    /**
     * { function_description }
     *
     * @param      <string>  $nodeValue  The node value
     * @return     array   contains [node->type, final node->value]
     */
    private function _define($nodeValue)
    {
        $v = substr($nodeValue, 1);
        switch ($nodeValue[0]) {
            case '%': return [T::DIRECTIVE, $v];
            case '#': return [T::COMMENT, $v];
            case '!':
            case "&":
            case "*":// TODO: handle tags like  <tag:clarkevans.com,2002:invoice>
                switch ($nodeValue[0]) {
                    case '!': $type = T::TAG;break;
                    case '&': $type = T::REF_DEF;break;
                    case '*': $type = T::REF_CALL;break;
                }
                $pos = strpos($v, ' ');
                if (is_bool($pos)) {
                    $this->name = $v;
                    return [$type, null];
                } else {
                    $this->name = strstr($v, ' ', true);
                    $n = new Node(trim(substr($nodeValue, $pos+1)), $this->line);
                    return [$type, $n->setParent($this)];
                }
            case '>': return [T::LITTERAL_FOLDED, null];
            case '|': return [T::LITTERAL, null];
            //TODO: complex mapping
            // case '?': //don't confuse with '!!set'
            // case ':':
            case '"':
            case "'":
                return $this->isQuoted($nodeValue) ? [T::QUOTED, $nodeValue] : [T::PARTIAL, $nodeValue];
            case "{":
            case "[":
                if ($this->isValidJSON($nodeValue))     return [T::JSON, $nodeValue];
                if ($this->isValidMapping($nodeValue))  return [T::MAPPING_SHORT, $nodeValue];
                if ($this->isValidSequence($nodeValue)) return [T::SEQUENCE_SHORT, $nodeValue];
                return [T::PARTIAL, $nodeValue];
            case "-":
                if (substr($nodeValue, 0, 3) === '---') return [T::DOC_START, new Node(trim(substr($nodeValue, 3)))];
                if (preg_match('/^-([\s\t]+(.*))?$/', $nodeValue, $matches)) {
                    if (isset($matches[1])) {
                        $n = new Node(trim($matches[1]), $this->line);
                        return [T::ITEM, $n->setParent($this)];
                    }
                    return [T::ITEM, null];
                }
            default:
                return [T::STRING, $nodeValue];
        }
    }

    public function __debugInfo()
    {
        $out = ['line'=>$this->line,
                'indent'=>$this->indent,
                'type' => T::getName($this->type),
                'value'=> $this->value];
        property_exists($this, 'name') ? $out['type'] .= "($this->name)" : null;
        return $out;
    }

    public function __sleep()
    {
        return ["value"];
    }

    /**
     * Determines if quoted.
     *
     * @param      string   $candidate  The candidate
     * @return     boolean  True if quoted, False otherwise.
     */
    public function isQuoted(string $candidate)
    {
        return (bool) preg_match("/(['".'"]).*?(?<![\\\\])\1$/ms', $candidate);
    }

    public function isValidJSON(string $candidate)
    {
        json_decode($candidate);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function isValidSequence(string $candidate)
    {
        return (bool) preg_match("/".(self::yamlSequence)."/i", $candidate);
    }

    public function isValidMapping(string $candidate)
    {
        return (bool) preg_match("/".(self::yamlMapping)."/i", $candidate);
    }

    public function getPhpValue()
    {
        if (is_null($this->value)) return null;
        switch ($this->type) {
            case T::EMPTY:return null;
            case T::BOOLEAN: return boolval($this->value);
            case T::NUMBER: return intval($this->value);
            case T::JSON: return json_encode($this->value);
            case T::QUOTED://fall through
            case T::REF_DEF://fall through
            case T::REF_CALL://fall through
            case T::TAG://fall through
            case T::COMMENT: //fall through
            case T::STRING: return strval($this->value);

            case T::MAPPING_SHORT://TODO
            //TODO : that's not robust enough, improve it
            case T::SEQUENCE_SHORT:
                return array_map("trim", explode(",", substr($this->value, 1, -1)));

            case T::DIRECTIVE://fall through
            case T::DOC_START://fall through
            // case T::KEY://fall through
            case T::ITEM:return $this->value->getPhpValue();

            case T::DOC_END: return;
            case T::PARTIAL:; // have a multi line quoted  string OR json definition
            default: throw new \Exception("Error can not get PHP type for ".T::getName($this->type), 1);
        }
    }
}
