<?php
namespace Dallgoot\Yaml;
use Dallgoot\Yaml\Types as T;
/**
* 
*/
class Node
{
    private $_parent = NULL;
    public $indent   = -1;
    public $line     = NULL;
    public $type     = NULL;
    public $value    = NULL;

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

    function __construct($nodeString=null, $line=null)
    {
        // echo self::yamlSequence;exit();
        $this->line = $line;
        if(is_null($nodeString)){
            $this->type = T::ROOT;
        }else{
            $this->parse($nodeString);
        }
    }
    public function setParent(Node $node)
    {
        $this->_parent = $node;
    }

    public function getParent($indent=null):Node
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
        }elseif ($current instanceof Node){
            if ($current->type === T::EMPTY) {
                $this->value = $child;
                return;
            }else{
                $this->value = new \SplQueue();
                $this->value->enqueue($current);
                $this->value->enqueue($child);
            }
        }elseif ($current instanceof \SplQueue) {
            $this->value->enqueue($child);
        }
        //modify type according to child
        switch ($child->type) {
            case T::KEY:  $this->type = T::MAPPING;break;
            case T::ITEM: $this->type = T::SEQUENCE;break;
            
            default: //do nothing
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
    //TODO : handle reference definitions/calls and tags and complex mappings
    private function parse(String $nodeString):Node
    {
        //permissive to tabs but replacement before processing
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);
        $this->indent = strspn($nodeValue , ' ');
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = T::EMPTY;
            $this->indent = 0;
        }elseif (substr($nodeValue, 0, 3) === '...'){
            $this->type = T::DOC_END;
        }elseif (preg_match('/^([^-{[][a-][^:#{["\'%!]*)\s*:[ \t]+(.*)?/', $nodeValue, $matches)) {
            $this->type = T::KEY; 
            $this->name = trim($matches[1]);
            if(isset($matches[2]) && !empty(trim($matches[2]))) {
                $n = new Node(trim($matches[2]), $this->line);
            }else{
                $n = new Node();
                $n->type = T::EMPTY;
            }
            $n->setParent($this);
            $this->value = $n;
        }else{//can be of another type according to VALUE
            list($this->type, $this->value) = $this->_define($nodeValue);
        }
        return $this;
    }

    private function _define($nodeValue)
    {
        $v = substr($nodeValue, 1);
        switch ($nodeValue[0]) {
            case '%': return [T::DIRECTIVE, $v];
            case '#': return [T::COMMENT, $v];
            case '!': return [T::TAG, $v];// TODO: handle tags
            case "&": return [T::REF_DEF, $v];//REFERENCE  //TODO
            case "*": return [T::REF_CALL, $v];
            case '>': return [T::LITTERAL_FOLDED, null];
            case '|': return [T::LITTERAL, null];
            //TODO: complex mapping
            // case '?': //don't confuse with '!!set'
            // case ':':
            case '"':
            case "'":
                return $this->isProperlyQuoted($nodeValue) ? [T::QUOTED, $nodeValue] : [T::PARTIAL, $nodeValue];
            case "{":
            case "[":
                if($this->isValidJSON($nodeValue))     return [T::JSON, $nodeValue];
                if($this->isValidMapping($nodeValue))  return [T::MAPPING_SHORT, $nodeValue];
                if($this->isValidSequence($nodeValue)) return [T::SEQUENCE_SHORT, $nodeValue];
                return [T::PARTIAL, $nodeValue]; 
            case "-":
                if(substr($nodeValue, 0, 3) === '---') return [T::DOC_START, new Node(trim(substr($nodeValue, 3)))];
                if (preg_match('/^-[ \t]*(.*)$/', $nodeValue, $matches)){
                    $n = new Node(trim($matches[1]), $this->line);
                    $n->setParent($this);
                    return [T::ITEM, $n];
                }
            default:
                return [T::STRING, $nodeValue];
        }
    }

    public function serialize():array
    {
        $name = property_exists($this, 'name') ? "($this->name)" : null;
        $out = ['node' => implode('|',[$this->line, $this->indent,T::getName($this->type).$name])];
        $v = $this->value;
        if($v instanceof \SplQueue) {
            $out['value'] = var_export($v, true);
            // for ($v->rewind(); $v->valid(); $v->next()) {
            //     $out['value'][] = $v->current()->serialize();//array_map(function($c){return $c->serialize();}, $this->children);
            // }
        }elseif($v instanceof Node){
            $out['value'] = $v->serialize();
        }else{
            $out['node'] .= "|".$v;
        }
        return $out;
    }

    public function __debugInfo() {
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

    public function isProperlyQuoted(string $candidate)
    {// check Node value to see if properly enclosed
        return (bool) preg_match("/(['".'"]).*?(?<![\\\\])\1$/ms', $candidate);
    }

    public function isValidJSON(string $candidate)
    {
        json_decode($candidate);
        return json_last_error() === JSON_ERROR_NONE; 
    }

    public function isValidSequence(string $candidate)
    {
        return (bool) preg_match("/".self::yamlSequence."/i", $candidate);
    }

    public function isValidMapping(string $candidate)
    {
        return (bool) preg_match("/".self::yamlMapping."/i", $candidate);
    }

    public function getPhpValue()
    {
        switch ($this->type) {
            case T::LITTERAL:;
            case T::LITTERAL_FOLDED:;
            // case T::NULL: 
            case T::EMPTY:return null;
            case T::BOOLEAN: return bool($this->value);
            case T::NUMBER: return intval($this->value);
            case T::JSON: return json_encode($this->value);
            case T::QUOTED:
            case T::REF_DEF:
            case T::REF_CALL:
            case T::TAG:;
            case T::STRING: return strval($this->value);

            case T::MAPPING_SHORT:
            case T::SEQUENCE_SHORT: return array_map(function($v){return trim($v);}, explode(",", substr($this->value, 1,-1)));
            
            case T::COMMENT:
            case T::DIRECTIVE:
            case T::DOC_START:
            case T::DOC_END:
            case T::DOCUMENT:
            // case T::ROOT:
            case T::KEY:; 
            case T::ITEM:return $this->value->getPhpValue();
            case T::PARTIAL:; // have a multi line quoted  string OR json definition
            default: throw new \Exception("Error can not get PHP type for ".T::getName($this->type), 1);
        }
    }
}