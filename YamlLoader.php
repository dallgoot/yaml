<?php
include 'Node.php';
use YamlLoader\Node as Node;
use YamlLoader\NODETYPES as NT;

class YamlLoader {
    public $_content = NULL;
    private $filePath = NULL;
    private $_debug = false;
    const INCLUDE_DIRECTIVE = false;
    const INCLUDE_COMMENTS = true;

    public function __construct($absolutePath = null, $options = NULL) {
        /*TODO: handle options:
                    - include_directive
                    - include_comments
                    - debug
                    - dont Exception on parsing Errors
        */
        if (!is_null($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    public function load(String $absolutePath):YamlLoader {
        $this->_debug && var_dump($absolutePath);
        $this->filePath = $absolutePath;
        if (!file_exists($absolutePath)) {
            throw new Exception("YamlLoader: file '$absolutePath' does not exists (or path is incorrect?)");
        }
        $prevADLE = ini_get("auto_detect_line_endings");
        !$prevADLE && ini_set("auto_detect_line_endings", true);
        $content = file($absolutePath, FILE_IGNORE_NEW_LINES);
        !$prevADLE && ini_set("auto_detect_line_endings", false);
        if (is_bool($content)) {
            throw new Exception("YamlLoader: file '$absolutePath' failed to be loaded (permission denied ?)");
        }
        $this->_content = $content;
        return $this;
    }

    public function parse($strContent = NULL) {
        $source = $strContent ? preg_split("/([^\n\r]+)/um", $strContent, NULL, PREG_SPLIT_DELIM_CAPTURE)
        : $this->_content;
        //TODO : be more permissive on $strContent values
        if (!is_array($source)) {
            throw new Exception('YamlLoader : content is not a string(maybe a file error?)');
        }
        $root = new Node();
        $previous = $root;
        //process structure
        foreach ($source as $lineNb => $lineString) {
            $n = new Node($lineString, $lineNb + 1);
            $parent = $previous;
            $deepest = $previous->getDeepestNode();
            if (in_array($n->type, NT::$LITTERALS)) {
                $deepest->getParent()->value = $n;
                continue;
            }
            if ($deepest->type === NT::PARTIAL) {
                $newValue = new Node($deepest->value.$lineString, $n->line);
                $mother = $deepest->getParent();
                $newValue->_parent = $mother; 
                $mother->value = $newValue; 
            }else{
                if($n->indent === 0) {
                    if($n->type === NT::EMPTY){
                        if($previous->type === NT::STRING) {
                            $parent = $previous->getParent();
                            $n->indent = $previous->indent;
                        }elseif(in_array($previous->type, NT::$LITTERALS)){
                            $parent = $previous;
                        }
                    }else{
                        $parent = $root;
                    }
                } elseif ($n->indent < $previous->indent) {
                    $parent = $previous->getParent($n->indent);
                } elseif ($n->indent === $previous->indent) {
                    $parent = $previous->getParent();
                } elseif ($n->indent > $previous->indent) {
                    switch ($previous->type) {
                        case NT::LITTERAL:
                        case NT::LITTERAL_FOLDED:
                            $n->type = NT::STRING;
                            break;
                        case NT::STRING:$n->type = NT::STRING;
                            $n->indent = $previous->indent;
                            $parent = $previous->getParent();
                    }
                }
                $parent->add($n);
                $previous = $n;
            }
        }
        var_dump($root);
        return $root;
        //exit();
        //return $this->_build($this->_defineRoot($root));
    }

    private function _build(Node $node) {
        //handling of comments , directives, tags should be here
         // if ($n->type === NT::COMMENT && !self::INCLUDE_COMMENTS) {continue;}
        $children = $node->value;

        if ($children instanceof \SplQueue) {// TODO :  adapt to PHP data types
            $this->_getBuildableChidren($children);
            switch ($children->current()->type) {
                case NT::KEY:      return $this->_map($children);
                case NT::ITEM:     return $this->_seq($children);
                case NT::LITTERAL: return $this->_litteral($children);
                //we are dealing with SCALAR values
                case NT::LITTERAL_FOLDED: 
                default: return $this->_litteral($children, true);
            }
        } elseif ($children instanceof Node) {
            switch ($children->type) {
                case 'value':
                    # code...
                    break;
                
                default:
                    # code...
                    break;
            }
        }
    }

    private function _litteral(SplQueue $children, $folded = false):string
    {
        $output = '';
        for ($children->rewind(); $children->valid() ; $children->next()) { 
            $output .= $children->current()->value.($folded ? PHP_EOL : " ");
        }
        return $output;
    }

    private function _map(SplQueue $children):StdClass {
        $out = new \StdClass;
        foreach ($children as $key => $child) {
            if (empty($child->name)) {
                throw new \Exception("YamlLoader: NODE has no keyname on line $child->line for '$this->filePath'");
            }
            $out->{$child->name} = $this->_build($child);
        }
        return $out;
    }

    private function _seq(SplQueue $children):array {
        $out = [];
        foreach ($children as $key => $child) {
           if(property_exists($child, "name")){
                $out[$child->name] = $this->_build($child);
           }else{
                $out[] = $this->_build($child);
           }
        }
        return $out;
    }


    private function _getBuildableChidren(SplQueue $children) {
        for ($children->rewind();  $children->valid() ; $children->next()) { 
            if(in_array($children->current()->type, NT::$NOTBUILDABLE)){
                $children->dequeue();
            } 
        }
    }

    //TODO : make it more robust by a diff of DOC_START and DOC_END
    // determines if there are  :
    // - mulitple documents  -> ROOT = array of docs
    // _ OR just one         -> ROOT = map|seq|litteral|scalar
    private function _defineRoot(Node $root) {
        $childrenList = $root->children;
        $childrenTypes = array_map(function ($n) {return $n->type;}, $root->children);
        $out = [];
        $pos = array_search(NT::DOC_END, $childrenTypes);
        $r = new Node();
        while ($pos !== false && $pos !== 0) {
            $n = new Node();
            $n->type = NT::DOCUMENT;
            $n->children = $this->_getBuildableChidren(array_splice($childrenList, $pos));
            $r->children[] = $n;
            $childrenTypes = array_slice($childrenTypes, $pos + 1);
            $pos = array_search(NT::DOC_END, $childrenTypes);
        }
        if (property_exists($r, 'children') && count($childrenList) > 0) {
            $r->children[] = $this->_getBuildableChidren($childrenList);
        } else {
            $root->children = $this->_getBuildableChidren($childrenList);
        }
        return property_exists($r, 'children') ? $r : $root;
    }

    public function checkChildrenCoherence(array $nodeChildren)
    {
        # code...
    }
}
