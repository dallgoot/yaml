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
        $this->line = $line;
        if (is_null($nodeString)) {
            $this->type = T::ROOT;
        } else {
            $this->parse($nodeString);
        }
    }
    public function setParent(Node $node):Node
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

    public function add(Node $child):void
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
                case T::SET_KEY:$this->value->type = T::SET;break;
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
    public function parse(String $nodeString):Node
    {
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);//permissive to tabs but replacement
        $this->indent = strspn($nodeValue, ' ');
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = T::EMPTY;
            $this->indent = 0;
        } elseif (substr($nodeValue, 0, 3) === '...') {//TODO: can have something after?
            $this->type = T::DOC_END;
        } elseif (preg_match('/^([[:alnum:]][[:alnum:]-_ ]*[ \t]*)(?::[ \t](.*)|:)$/', $nodeValue, $matches)) {
            $this->type = T::KEY;
            $this->name = trim($matches[1]);
            $keyValue = isset($matches[2]) ? trim($matches[2]) : null;
            if (is_null($keyValue)) {
                $n = new Node('', $this->line);// $n->type = T::EMPTY;
            } else {
                $n = new Node($keyValue, $this->line);
                $hasComment = strpos($keyValue, ' #');
                if (!is_bool($hasComment)) {
                    $tmpNode = new Node(trim(substr($keyValue, 0, $hasComment)), $this->line);
                    if ($tmpNode->type !== T::PARTIAL) {
                        $comment = new Node(trim(substr($keyValue, $hasComment+1)), $this->line);
                        $this->add($comment);
                        $n = $tmpNode;
                    }
                }
            }
            $n->indent = $this->indent + strlen($this->name);
            $this->add($n);
        } else {//NOTE: can be of another type according to parent
            list($this->type, $value) = $this->_define($nodeValue);
            is_object($value) ? $this->add($value) : $this->value = $value;
        }
        return $this;
    }

    /**
     *  Set the type and value according to first character
     *
     * @param      string  $nodeValue  The node value
     * @return     array   contains [node->type, final node->value]
     */
    private function _define($nodeValue):array
    {
        $v = substr($nodeValue, 1);
        switch ($nodeValue[0]) {
            case '%': return [T::DIRECTIVE, ltrim($v)];
            case '#': return [T::COMMENT, ltrim($v)];
            case '!':
            case "&":
            case "*": return $this->_onNodeAction($nodeValue);
            case '>': return [T::LITTERAL_FOLDED, null];
            case '|': return [T::LITTERAL, null];
            //TODO: complex mapping
            case '?': $this->name = new Node(ltrim($v), $this->line); return [T::SET_KEY, null];
            case ':': return [T::SET_VALUE, new Node(ltrim($v), $this->line)];
            case '"':
            case "'": return (bool) preg_match("/(['".'"]).*?(?<![\\\\])\1$/ms', $nodeValue) ?
                                [T::QUOTED, $nodeValue] : [T::PARTIAL, $nodeValue];
            case "{":
            case "[": return $this->_onObject($nodeValue);
            case "-": return $this->_onMinus($nodeValue);
            default:
                return [T::STRING, $nodeValue];
        }
    }

    private function _onObject($nodeValue):array
    {
        json_decode($nodeValue);
        if (json_last_error() === JSON_ERROR_NONE)
            return [T::JSON, $nodeValue];
        if ((bool) preg_match("/".(self::yamlMapping)."/i", $nodeValue))
            return [T::MAPPING_SHORT, $nodeValue];
        if ((bool) preg_match("/".(self::yamlSequence)."/i", $nodeValue))
            return [T::SEQUENCE_SHORT, $nodeValue];
        return [T::PARTIAL, $nodeValue];
    }

    private function _onMinus($nodeValue):array
    {
        if (substr($nodeValue, 0, 3) === '---') {
            $n = new Node(trim(substr($nodeValue, 3)), $this->line);
            $n->indent = $this->indent+4;
            return [T::DOC_START, $n->setParent($this)];
        }
        if (preg_match('/^-([ \t]+(.*))?$/', $nodeValue, $matches)) {
            if (isset($matches[1])) {
                $n = new Node(trim($matches[1]), $this->line);
                return [T::ITEM, $n->setParent($this)];
            }
            return [T::ITEM, null];
        }
        return [T::STRING, $nodeValue];
    }

    private function _onNodeAction($nodeValue):array
    {
        // TODO: handle tags like  <tag:clarkevans.com,2002:invoice>
        $v = substr($nodeValue, 1);
        switch ($nodeValue[0]) {
            case '!': $type = T::TAG;break;
            case '&': $type = T::REF_DEF;break;
            case '*': $type = T::REF_CALL;break;
        }
        $pos = strpos($v, ' ');
        $this->name = is_bool($pos) ? $v : strstr($v, ' ', true);
        $n = is_bool($pos) ? null : (new Node(trim(substr($nodeValue, $pos+1)), $this->line))->setParent($this);
        return [$type, $n];
    }

    public function __debugInfo():array
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


    public function getPhpValue()
    {
        switch ($this->type) {
            case T::EMPTY:return null;
            case T::BOOLEAN: return boolval($this->value);
            case T::NUMBER: return intval($this->value);
            case T::JSON: return json_encode($this->value);
            case T::QUOTED://fall through
            case T::REF_CALL://fall through
            case T::STRING: return strval($this->value);

            case T::MAPPING_SHORT://TODO
            //TODO : that's not robust enough, improve it
            case T::SEQUENCE_SHORT:
                return array_map("trim", explode(",", substr($this->value, 1, -1)));

            case T::DOC_START://fall through

            case T::DOC_END: return;
            case T::PARTIAL:; // have a multi line quoted  string OR json definition
            default: throw new \Exception("Error can not get PHP type for ".T::getName($this->type), 1);
        }
    }
}
