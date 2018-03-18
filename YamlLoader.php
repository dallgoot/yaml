<?php
namespace YamlLoader;
include 'Node.php';
use YamlLoader\NODETYPES as NT;
use YamlLoader\Node as Node;

class YamlLoader
{
    public $_content = NULL;
    private $filePath = NULL;
    const INCLUDE_DIRECTIVE = false;
    const INCLUDE_COMMENTS = true;

    public function __construct($absolutePath=null, $options=NULL)
    {
        //TODO/ handle options
        if (!is_null($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    public function load(String $absolutePath)
    {
        if(!file_exists($absolutePath)) throw new Exception("YamlLoader: file '$absolutePath' does not exists (or path is incorrect?)");
        $prevADLE = ini_get("auto_detect_line_endings"); 
        !$prevADLE && ini_set("auto_detect_line_endings", true);
        $this->filePath = $absolutePath;
        $content = file($absolutePath, FILE_IGNORE_NEW_LINES);
        if(is_bool($content)) throw new Exception("YamlLoader: file '$absolutePath' fail to be loaded (permission denied ?)");
        !$prevADLE && ini_set("auto_detect_line_endings", false);
        $this->_content = $content;
        return $this; 
    }

    public function parse($strContent=NULL) {
        $source = $strContent ? preg_split("/([^\n\r]+)/um", $strContent, NULL,  PREG_SPLIT_DELIM_CAPTURE) 
                                : $this->_content;
        //TODO : be more permissive on $strContent values
        if (!is_array($source)) throw new Exception('YamlLoader : content is not a string(maybe a file error?)');
        //process structure
        $root = new Node();
        $previous = $root;
        foreach ($source as $lineNb => $lineString) {
            $n = new Node($lineString, $lineNb);
            $parent = $previous;
            if($n->type === NT::COMMENT && !self::INCLUDE_COMMENTS){
                continue;
            }
            if ($n->indent < $previous->indent) {
                $parent = ($n->indent === 0) ? $root : $previous->getParent($n->indent);
            }elseif($n->indent === $previous->indent) {
                $parent = ($n->indent === 0) ? $root : $previous->getParent();
            }elseif ($n->indent > $previous->indent) {
                switch ($previous->type) {
                    case NT::KEY_BLOCK:
                    case NT::KEY_BLOCK_FOLDED:
                    case NT::KEY_PARTIAL:
                    case NT::ITEM_PARTIAL:
                        $n->type = NT::STRING;
                        break;
                    case NT::STRING: $n->type = NT::STRING;
                                     $n->indent = $previous->indent;
                                     $parent = $previous->getParent();
                }
            }
            $parent->add($n);
            $previous = $n;
        }
        // var_dump($root);
        return $this->_build($root);
    }

    private function _build(Node $node)
    {
        if(!property_exists($node, 'children')){// TODO :  adapt to PHP data types
            return $node->value;
        }
        $action = "_map";
        $target = $node->children[0]; 
        if ($node->type === NT::ROOT) {
            $node = $this->_defineRoot($node);
        }
        switch ($node->children[0]->type) {
            case NT::ITEM_VALUE:
            case NT::ITEM_NOVALUE:$action = "_seq";
                break;
            case NT::KEY_VALUE:
            case NT::KEY_NOVALUE:$action = "_map";
                break;
            case NT::ITEM_PARTIAL:
            case NT::KEY_PARTIAL:return $this->_partial($node);
                break;
            default: 
                $action = "_paragraph";break;
        }
        return $this->{$action}($this->_getBuildableChidren($node->children));
    }

    //TODO: handle state : FOLDED or NOT
    private function _paragraph(array $childrenList) {
        $getValue = function($n){ return $n->value; };
        return join(PHP_EOL, array_map($getValue, $childrenList)); 
    }

    private function _map(array $buildableList) {
        $out = new \StdClass;
        foreach ($buildableList as $key => $child) {
            if (empty($child->name)) {
                throw new \Exception("YamlLoader: key has no name on line ".$this->line."for ".$this->filePath);
            }
            $out->{$child->name} = $this->_build($child);
        }
        return $out;
    }

    private  function _seq(array $buildableList) {
        $out = [];
        foreach ($buildableList as $key => $child) {
            switch ($child->type) {
                case NT::ITEM_VALUE:
                    $n = new Node($child->value, $child->line);
                    if(property_exists($child, 'children')){
                        array_unshift($child->children, $n); 
                        $out[] = $this->_map($this->_getBuildableChidren($child->children));
                    }else{
                        if (!empty($n->value)) {
                            $out[] = $child->value;
                        }else{
                            $out[$child->name] = $child->value;
                        }
                    }
                    break;
                case NT::ITEM_NOVALUE:
                    $out[] = $this->_map($this->_getBuildableChidren($child->children));
                    break;
                case NT::ITEM_PARTIAL:
                    $out[] = $this->_partial($child);
                    break;
                default:
            }
        }
        return $out;
    }

    // TODO : implement
    private function _partial(Node $node)
    {
        // var_dump('_partial');
        # code...
    }

    private function _getBuildableChidren(array $childrenList)
    {
        $notBuildable = [NT::DIRECTIVE, NT::ROOT, NT::DOC_START, NT::DOC_END, NT::COMMENT, NT::EMPTY];
        $filterFunc = function($child) use($notBuildable) {return !in_array($child->type , $notBuildable);};
        return array_values(array_filter($childrenList, $filterFunc));
    }

    //TODO : make it more robust by a diff of DOC_START and DOC_END
        // determines if there are  : 
        // - mulitple documents  -> ROOT = array of docs 
        // _ OR just one         -> ROOT = map/seq/paragraph
    private function _defineRoot(Node $root)
    {
        $childrenList = $root->children;
        $childrenTypes = array_map(function($n){ return $n->type; }, $root->children);
        $out = [];
        $pos = array_search(NT::DOC_END, $childrenTypes);
        $r = new Node();
        while($pos !== false && $pos !== 0){
            $n = new Node();
            $n->type = NT::DOCUMENT;
            $n->children = $this->_getBuildableChidren(array_splice($childrenList, $pos)); 
            $r->children[] = $n;
            $childrenTypes = array_slice($childrenTypes, $pos+1);
            $pos = array_search(NT::DOC_END, $childrenTypes);
        }
        if (property_exists($r, 'children') && count($childrenList) > 0)
        {
            $r->children[] = $this->_getBuildableChidren($childrenList);
        }else{
            $root->children = $this->_getBuildableChidren($childrenList);
        }
        return property_exists($r, 'children') ? $r : $root;
    }

    // private function _childrenConsistency($childrenList)
    // {
    //     $allTypes = array_map(function($n){return $n->type;}, $childrenList);
    //     $isAllowedType = function($t){return !in_array($t , [NT::DIRECTIVE,
    //                                                         NT::DOC_START,
    //                                                         NT::DOC_END,
    //                                                         NT::COMMENT,
    //                                                         NT::ROOT,
    //                                                         NT::EMPTY]);
    //     };
    //     $allowedTypes = array_filter($allTypes,$isAllowedType);
    //     $distinctTypes = array_unique($allowedTypes, SORT_NUMERIC);
    //     if(count($allowedTypes) !== count($distinctTypes)){
    //         $line = (string) $node->line;
    //         throw new \Exception("YamlLoader: parsing error on line {$line}: children are not of same type");
    //     }
    // }

    // TODO : implement
    // public function dump($fileName)
    // {
    //     # code...
    // }
}
