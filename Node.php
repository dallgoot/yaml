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
    const REFERENCE = 152;
}
use YamlLoader\NODETYPES as NT;
/**
* 
*/
class Node
{
    private $_parent = NULL;
    public $_nodeTypes = [];

    public $indent   = -1;
    public $line     = NULL;
    public $name     = NULL;
    public $type     = NULL;
    public $value    = NULL;

    function __construct($nodeString=null, $line=null)
    {
        $this->_nodeTypes = array_flip((new \ReflectionClass('YamlLoader\NODETYPES'))->getConstants());
        if(is_null($nodeString)){
            $this->type = NT::ROOT;
        }else{
            $this->parse($nodeString);
            $this->line = $line;
        }
    }

    public function getParent($indent=null)
    {
        $cursor = $this->_parent;
        if(!is_null($indent) && is_int($indent))
        {//TODO : make sure _parent is of following types : KEY, ITEM (COMPLEX???)
            while(!is_null($cursor) && $indent !== $cursor->indent)
            {
                $cursor = $cursor->_parent;
            }
        }
        return $cursor; 
    }

    public function add(Node $child)
    {
        $child->_parent = $this;
        //TODO : is it necessary to adjust type according to parent ???
        switch ($child->type) {
            //TODO:  handle those type to create new document inside ROOT
            //ignore those types for now
            case NT::DIRECTIVE:
            case NT::DOC_START:
            case NT::DOC_END:
                break;
            case NT::COMMENT:
                property_exists($this, '_comments') || $this->_comments = [];
                $this->_comments[(string)$child->line] = $child->value;
                break;
            
            default:
                property_exists($this, 'children') || $this->children = [];
                $this->children[] = $child;
                break;
        }
    }
    /**
    *  CAUTION : the types assumed here are NOT FINAL : they CAN be adjusted according to parent
    */
    //TODO : handle reference definitions/calls and tags and complex mappings
    //EVOLUTION:  if keyname contains unauthorized character for PHP property name : replace with '_'  ???
    private function parse(String $nodeString){
        //permissive to tabs but replacement before processing
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);
        $this->indent = 0;
        while ($this->indent < mb_strlen($nodeValue) && $nodeValue[$this->indent]===' ') { 
            $this->indent++;
        }
        $n = new Node(null, $this->line);//$nodeValue;
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = NT::EMPTY;
            $this->indent = 0;
        }elseif(substr($nodeValue, 0, 3) === '...'){
            $this->type = NT::DOC_END;
        }elseif (preg_match('/^[ \t]*([^:#]+)\s*:[ \t]*(.+)?/', ltrim($nodeValue), $matches)) {
            $this->type = NT::KEY; 
            $this->name = trim($matches[1]);
            isset($matches[2]) ? $n->parse(trim($matches[2])) : $n->type = NT::NULL; 
            $this->value = $n;
        }else{//can be of another type according to VALUE
            switch ($nodeValue[0]) {
                case '%': $this->type = NT::DIRECTIVE;break;
                case '#': $this->type = NT::COMMENT;
                    $this->value = $nodeValue;
                    break;
                 // TODO: handle tags
                case '!': $this->type = NT::TAG;break;
                //LITTERAL //TODO handles LITTERAL lines with only spaces
                case '>': $this->type = NT::LITTERAL_FOLDED;break;
                case '|': $this->type = NT::LITTERAL;break;
                //REFERENCE  //TODO
                case "&":
                case "*":break;
                case "-":
                    if(substr($nodeValue, 0, 3) === '---'){
                        $this->type = NT::DOC_START;
                        $n->parse(substr($nodeValue, 3));
                    }elseif (preg_match('/^-[ \t]*(.*)$/', $nodeValue, $matches)) {
                        $this->type = NT::ITEM;
                        $n->parse($matches[1]);
                    }else{
                        $this->type = NT::STRING;
                        $n->parse($nodeValue);
                    }
                    $this->value = $n;//should contains any tags
                    break;
                case '"':
                case "'":
                    if($this->isProperlyQuoted($nodeValue)){
                        $this->type = NT::QUOTED;
                    }else{
                        $this->type = NT::PARTIAL;
                        $this->delimiter = $nodeValue[0];
                    }
                    $this->value = $nodeValue;
                    break;
                case "{":
                case "[": //TODO: handle JSON in value
                    if($this->isValidJSON($nodeValue)){
                        $this->type = NT::JSON;
                    }else{
                        $this->type = NT::PARTIAL;
                        $this->delimiter = $nodeValue[0];
                    }
                    $this->value = $nodeValue;
            }
        }
    }

    public function serialize()
    {
        $out = ['node' => implode('|',[$this->line, $this->_nodeTypes[$this->type], "'".$this->name."'", $this->value])];
        if(property_exists($this, 'children')){
            $out['children'] = array_map(function($c){return $c->serialize();}, $this->children);
        }
        return $out;
    }

    public function __debugInfo() {
        return $this->serialize();
    }

    public function isProperlyQuoted($candidate)
    {// check Node value to see if properly enclosed or formed
        $regex = <<<'EOD'
/(['"]).*?(?<![\\\\])\1$/ms
EOD;
        var_dump($candidate);
        return preg_match($regex, $candidate);
    }

    public function isValidJSON($candidate)
    {// check Node value to see if properly enclosed or formed
        return json_decode($candidate) !== null;
    }
}