<?php
namespace YamlLoader;
class NODETYPES{
    const DIRECTIVE  = 0;
    const DOC_START = 1;
    const DOC_END = 2;
    const DOCUMENT = 4;
    const COMMENT = 8;
    const EMPTY   = 16;
    const ROOT    = 32;
    // single line or have children
    const KEY = 42; 
    const ITEM = 52;

    const PARTIAL = 62; // have a multi line quoted  string OR json definition
    const LITTERAL = 72;
    const LITTERAL_FOLDED = 82;

    const NULL    = 92;
    const STRING    = 102;
    const BOOLEAN = 112;
    const NUMBER  = 122;
    const TAG = 132;
    const JSON = 142;
    
    const QUOTED = 148;
    const REF_DEF = 152;
    const REF_CALL = 164;
    public static $NOTBUILDABLE = [self::DIRECTIVE,
                                    self::ROOT,
                                    self::DOC_START,
                                    self::DOC_END,
                                    self::COMMENT,
                                    self::EMPTY,
                                    self::TAG];
    public static $LITTERALS = [self::LITTERAL, self::LITTERAL_FOLDED];
}
use YamlLoader\NODETYPES as NT;
/**
* 
*/
class Node
{
    public $_parent = NULL;
    public $_nodeTypes = [];

    public $indent   = -1;
    public $line     = NULL;
    public $name     = NULL;
    public $type     = NULL;
    public $value    = NULL;

    function __construct($nodeString=null, $line=null)
    {
        $this->_nodeTypes = array_flip((new \ReflectionClass('YamlLoader\NODETYPES'))->getConstants());
        $this->line = $line;
        if(is_null($nodeString)){
            $this->type = NT::ROOT;
        }else{
            $this->parse($nodeString);
        }
    }

    public function getParent($indent=null)
    {   
        if (is_null($indent)) {
             return $this->_parent ?? $this; 
        } else {
            $cursor = $this;
            while ($cursor->indent >= $indent) {
                $cursor = $cursor->_parent;
            }
            return $cursor;
        } 
    }

    public function add(Node $child)
    {
        $child->_parent = $this;
        if (!($this->value instanceof \SplQueue)) {
            $nq = new \SplQueue();
            if ($this->value instanceof Node && $this->value->type !== NT::EMPTY) {
                $nq->enqueue($this->value);
            }
            $this->value = $nq;
        }
        $this->value->enqueue($child);
    }


    public function getDeepestNode()
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
    //EVOLUTION:  if keyname contains unauthorized character for PHP property name : replace with '_'  ???
    private function parse(String $nodeString){
        //permissive to tabs but replacement before processing
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);
        $this->indent = strspn($nodeValue , ' ');
        $n = new Node(null, $this->line);//$nodeValue;
        $n->_parent = $this;
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = NT::EMPTY;
            $this->indent = 0;
        }elseif (substr($nodeValue, 0, 3) === '...'){
            $this->type = NT::DOC_END;
        }elseif (preg_match('/^([^-][^:#{["\']*)\s*:[ \t]*(.*)?/', ltrim($nodeValue), $matches)) {
            $this->type = NT::KEY; 
            $this->name = trim($matches[1]);
            isset($matches[2]) ? $n->parse(trim($matches[2])) : $n->type = NT::NULL; 
            $this->value = $n;
        }else{//can be of another type according to VALUE
            list($this->type, $this->value) = $this->_define($nodeValue);
        }
        return $this;
    }

    private function _define($nodeValue)
    {
        $n = new Node(null, $this->line);
        $v = substr($nodeValue, 1);
        $n->_parent = $this;
        switch ($nodeValue[0]) {
            case '%': return [NT::DIRECTIVE, $v];
            case '#': return [NT::COMMENT, $v];
            // TODO: handle tags
            case '!': return [NT::TAG, $v];
            //TODO handles LITTERAL lines with only spaces
            case '>': return [NT::LITTERAL_FOLDED, null];
            case '|': return [NT::LITTERAL, null];
            //REFERENCE  //TODO
            case "&": return [NT::REF_DEF, $v];
            case "*": return [NT::REF_CALL, $v];
            case "-":
                if(substr($nodeValue, 0, 3) === '---') return [NT::DOC_START, substr($nodeValue, 3)];
                if (preg_match('/^-[ \t]*(.*)$/', $nodeValue, $matches)) return [NT::ITEM, $n->parse($matches[1])];
                return [NT::STRING, $nodeValue];
            case '"':
            case "'":
            case "{":
            case "[":
                if ($this->isProperlyQuoted($nodeValue)) return [NT::QUOTED, $nodeValue];
                if ($this->isValidJSON($nodeValue))      return [NT::JSON, $nodeValue];
                return [NT::PARTIAL, $nodeValue];
            default:
                return [NT::STRING, $nodeValue];
        }
    }

    public function serialize():array
    {
        $out = ['node' => implode('|',[$this->line, $this->_nodeTypes[$this->type], "'".$this->name."'"])];
        $v = $this->value;
        if($v instanceof \SplQueue) {
            $out['value'] = [];
            for ($v->rewind(); $v->valid(); $v->next()) {
                $out['value'][] = $v->current()->serialize();//array_map(function($c){return $c->serialize();}, $this->children);
            }
        }elseif($v instanceof Node){
            $out['value'] = $v->serialize();
        }else{
            $out['node'] .= "|".$v;
        }
        return $out;
    }

    public function __debugInfo() {
        return $this->serialize();
    }

    public function isProperlyQuoted($candidate)
    {// check Node value to see if properly enclosed or formed
        $regex = "/(['".'"]).*?(?<![\\\\])\1$/ms';
        // var_dump($candidate);
        return preg_match($regex, $candidate);
    }

    public function isValidJSON($candidate)
    {// check Node value to see if properly enclosed or formed
        json_decode($candidate);
        return json_last_error() === JSON_ERROR_NONE; 
    }
}