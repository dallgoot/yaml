<?php
namespace YamlLoader;
class NODETYPES{
    const DIRECTIVE  = 0;
    const DOC_START = 1;
    const DOC_END = 2;
    const DOCUMENT = 40;
    const COMMENT = 3;
    const NULL    = 4;
    const ROOT    = 16;
    const KEY_NOVALUE= 32;
    const ITEM_NOVALUE = 34;
    const ITEM_VALUE = 35;
    const ITEM_PARTIAL = 40;
    const STRING    = 64;
    const BOOLEAN = 128;
    const NUMBER  = 256;
    const KEY_VALUE  = 512;
    const KEY_BLOCK = 1024;
    const KEY_BLOCK_FOLDED = 1024;
    const EMPTY   =  2048;
    const KEY_PARTIAL = 4096;
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
            $this->_parse($nodeString);
            $this->line = $line;
        }
    }

    public function getParent($indent=null)
    {
        $cursor = $this->_parent;
        if(!is_null($indent) && is_int($indent))
        {
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
    private function _parse(String $nodeString){
        //permissive to tabs but replacement before processing
        $nodeValue = preg_replace("/\t/m", " ", $nodeString);
        if(empty($nodeValue))//|| preg_match('/^[\r\n]$/', $nodeValue))
        {//TODO handles KEY_BLOCK lines with only spaces
            $this->type = NT::EMPTY;
            $this->indent = 0;
            return;
        }
        $this->indent = 0;
        while ($this->indent < mb_strlen($nodeValue) && $nodeValue[$this->indent]===' ') { 
            $this->indent++;
        }
        $this->value = $nodeValue;
        $noIndentString = substr($nodeString, $this->indent);
        switch(true)
        {
            case substr($nodeString, 0, 3) === '---': $this->type = NT::DOC_START;break;
            case substr($nodeString, 0, 3) === '...': $this->type = NT::DOC_END;break;
            // allowed : 'YAML', 'TAG'  AND ONLY once
            case $nodeString[0] === '%': $this->type = NT::DIRECTIVE;break;
            case $nodeString[0] === '#': $this->type = NT::COMMENT;break;
            //TODO : edge case if parent is KEY_BLOCK : this is NOT an ITEM
            case preg_match('/^-[ \t]+(.*)$/', $noIndentString, $matches):
                $this->value = $matches[1];
                if(empty($this->value)) {
                    $this->type = NT::ITEM_NOVALUE; 
                }else{
                    $this->type = NT::ITEM_VALUE;
                    $isKEY_VALUE = strpos($this->value,': '); 
                    if($isKEY_VALUE !== FALSE || $this->value[-1]===':'){
                        list($this->name, $this->value) = explode(':', $this->value);
                    }
                }
                break;
            case preg_match('/^[ \t]*([^: ]+)\s*:[ \t]*$/', $noIndentString, $matches):
                $this->type = NT::KEY_NOVALUE;
                $this->name = trim($matches[1]);
                break;
            //TODO : edge case if parent is KEY_BLOCK : this is NOT an KEY_VALUE
            case preg_match('/^[ \t]*([^: ]+)\s*:[ \t]*(.+)?/', $noIndentString, $matches):
                $this->type = NT::KEY_VALUE; 
                $this->name = trim($matches[1]);
                $value = ltrim($matches[2]); 
                //can be of another type according to VALUE
                switch ($value[0]) {
                    //TODO: handle folded (or NOT) lines
                    case '>': $this->type = NT::KEY_BLOCK_FOLDED;break;
                    case '|': $this->type = NT::KEY_BLOCK;break;
                    case "&"://TODO : handle reference declaration
                        break;
                    case '"':
                    case "'":
                    case "{":
                    case "[": //TODO: handle JSON in value
                        $closingMatch = ["{" => "}", "[" => "]", "'" => "'", '"' => '"'][$value[0]];
                        $this->type = ($value[-1] !== $closingMatch) ? NT::KEY_PARTIAL : NT::KEY_VALUE;
                        if ($this->type === NT::KEY_PARTIAL) $this->delimiter = $closingMatch;
                        break;
                    default: // default type and value set above
                        $this->value = $value;
                }
                break;
            default :
                $this->type = NT::STRING;
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
}