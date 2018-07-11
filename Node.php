<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Types as T, Regex as R};

class Node
{
    public $indent = -1;
    public $line;
    public $type;
    /** @var Node|\SplQueue|null|string */
    public $value;
    private $_parent;

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
        if (in_array($this->type, T::$LITTERALS)) {
            $child->type = T::SCALAR;
            unset($child->name);
        }
        if (is_null($current)) {
            $this->value = $child;
            return;
        } elseif ($current instanceof Node) {
            $this->value = new \SplQueue();
            $this->value->setIteratorMode(\SplDoublyLinkedList::IT_MODE_KEEP);
            $this->value->enqueue($current);
        }
        $this->value->enqueue($child);
        //modify type according to child
        if ($this->value instanceof \SplQueue && !property_exists($this->value, "type")) {
            switch ($child->type) {
                case T::KEY:    $this->value->type = T::MAPPING;break;
                case T::ITEM:   $this->value->type = T::SEQUENCE;break;
                case T::SCALAR: $this->value->type = $this->type;break;
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
        } elseif (preg_match('/^([[:alnum:]_][[:alnum:]_ -]*[ \t]*)(?::[ \t](.*)|:)$/', $nodeValue, $matches)) {
            $this->_onKey($matches);
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
            case '#': return [T::COMMENT, ltrim($v)];
            case "-": return $this->_onMinus($nodeValue);
            case '"': //fall through
            case "'": return (bool) preg_match("/(['".'"]).*?(?<![\\\\])\1$/ms', $nodeValue) ?
                                [T::QUOTED, $nodeValue] : [T::PARTIAL, $nodeValue];
            case "{": //fall through
            case "[": return $this->_onObject($nodeValue);
            case '!': //fall through
            case "&": //fall through
            case "*": return $this->_onNodeAction($nodeValue);
            case '%': return [T::DIRECTIVE, ltrim($v)];
            case '?': return [T::SET_KEY,   empty($v) ? null : new Node(ltrim($v), $this->line)];
            case ':': return [T::SET_VALUE, empty($v) ? null : new Node(ltrim($v), $this->line)];
            case '>': $this->value = new \SplQueue;return [T::LITTERAL_FOLDED, null];
            case '|': $this->value = new \SplQueue;return [T::LITTERAL, null];
            default:
                return [T::SCALAR, $nodeValue];
        }
    }

    private function _onKey($matches)
    {
        $this->type = T::KEY;
        $this->name = trim($matches[1]);
        $keyValue = isset($matches[2]) ? trim($matches[2]) : null;
        if (!empty($keyValue)) {
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
            $n->indent = $this->indent + strlen($this->name);
            $this->add($n);
        }
    }

    private function _onObject($value):array
    {
        json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE)  return [T::JSON, $value];
        if ((bool) preg_match(R::MAPPING, $value))  return [T::MAPPING_SHORT, $value];
        if ((bool) preg_match(R::SEQUENCE, $value)) return [T::SEQUENCE_SHORT, $value];
        return [T::PARTIAL, $value];
    }

    private function _onMinus($nodeValue):array
    {
        if (substr($nodeValue, 0, 3) === '---') {
            $rest = trim(substr($nodeValue, 3));
            if (empty($rest)) return [T::DOC_START, null];
            $n = new Node($rest, $this->line);
            $n->indent = $this->indent+4;
            return [T::DOC_START, $n->setParent($this)];
        }
        if (preg_match('/^-([ \t]+(.*))?$/', $nodeValue, $matches)) {
            if (isset($matches[1]) && !empty(trim($matches[1]))) {
                $n = new Node(trim($matches[1]), $this->line);
                return [T::ITEM, $n->setParent($this)];
            }
            return [T::ITEM, null];
        }
        return [T::SCALAR, $nodeValue];
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

    public function getPhpValue()
    {
        $v = $this->value;
        if (is_null($v)) return null;
        switch ($this->type) {
            case T::EMPTY:  return null;
            case T::JSON:   return json_decode($v, JSON_PARTIAL_OUTPUT_ON_ERROR);
            case T::QUOTED: return substr($v, 1, -1);
            case T::REF_CALL://fall through
            case T::SCALAR: return $this->getScalar($v);
            case T::MAPPING_SHORT://TODO : that's not robust enough, improve it
                return $this->getShortMapping(substr($this->value, 1, -1));
            case T::SEQUENCE_SHORT://TODO : that's not robust enough, improve it
                return array_map("trim", explode(",", substr($this->value, 1, -1)));
            default:
                throw new \Exception("Error can not get PHP type for ".T::getName($this->type), 1);
        }
    }

    private function getScalar($v)
    {
        switch (strtolower($v)) {
            case 'yes':   return true;
            case "no":    return false;
            case 'true'://fall through
            case 'false': return boolval($v);
            case 'null':  return null;
            case '.inf':  return INF;
            case '-.inf': return -INF;
            case '.nan':  return NAN;
            default: //TODO: make number type detection more robust
                switch (true) {
                    case preg_match("/^(0o\d+)$/i", $v):
                        return intval(base_convert($v, 8, 10));
                    case preg_match("/^(0x[\da-f]+)$/i", $v):
                        return intval(base_convert($v, 16, 10));
                    case preg_match("/^([\d.]+e[-+]\d{1,2})$/", $v)://fall through
                    case preg_match("/^([-+]?(?:\d+|\d*.\d+))$/", $v):
                        return is_bool(strpos($v, '.')) ? intval($v) : floatval($v);
                    default:
                }
                return strval($v);
        }
    }

    // private function getNumber($v)
    // {

    // }

    private function getShortMapping($mappingString)
    {
        $out = new \StdClass();
        foreach (explode(',', $mappingString) as $value) {
            list($keyName, $keyValue) = explode(':', $value);
            $out->{trim($keyName)} = trim($keyValue);
        }
        return $out;
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
}
