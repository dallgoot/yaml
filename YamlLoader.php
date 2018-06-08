<?php
include 'Node.php';
use YamlLoader\Node as Node;
use YamlLoader\NODETYPES as NT;

class YamlLoader {
    public $_content = NULL;
    private $filePath = NULL;
    private $_debug = true;
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
        $emptyLines = [];
        //process structure
        foreach ($source as $lineNb => $lineString) {
            $n = new Node($lineString, $lineNb + 1);
            // $this->_debug && var_dump($n);
            $parent = $previous;
            $deepest = $previous->getDeepestNode();
            if (in_array($n->type, NT::$LITTERALS)) {
                $deepestParent = $deepest->getParent();
                if ($deepest->type === NT::EMPTY && 
                    $deepestParent->type === NT::KEY) {
                    $deepestParent->value = $n;
                }else{
                    $deepest->value = $n;
                }
                continue;
            }
            if($n->type === NT::EMPTY){
                if (in_array($deepest->type, NT::$LITTERALS)) {
                    $n->setParent($deepest);
                    $emptyLines[] = $n;
                }else if($previous->type === NT::STRING){
                    $n->setParent($previous->getParent());
                    $emptyLines[] = $n;
                }
                continue;
            }else{
                foreach ($emptyLines as $key => $node) {
                    $node->getParent()->add($node);
                }
                $emptyLines = [];
            }
            if ($deepest->type === NT::PARTIAL) {
                $newValue = new Node($deepest->value.$lineString, $n->line);
                $mother = $deepest->getParent();
                $newValue->setParent($mother); 
                $mother->value = $newValue; 
            }else{
                if($n->indent === 0) {
                    $parent = $root;
                } elseif ($n->indent < $previous->indent) {
                    $parent = $previous->getParent($n->indent);
                } elseif ($n->indent === $previous->indent) {
                    $parent = $previous->getParent();
                } elseif ($n->indent > $previous->indent) {
                    switch ($deepest->type) {
                        case NT::LITTERAL:
                        case NT::LITTERAL_FOLDED:
                            $n->type = NT::STRING;
                            $n->value = trim($lineString);
                            unset($n->name);
                            $parent = $deepest;
                            break;
                        case NT::EMPTY:
                        case NT::STRING:
                            if ($n->type === NT::STRING) {
                                $deepest->type = NT::STRING;
                                $deepest->value .= PHP_EOL.$n->value;
                                continue 2;
                            }
                    }
                }
                $parent->add($n);
                $previous = $n;
            }
        }
        $this->_debug && var_dump($root);
        try {
            $out = $this->_build($root);
        } catch (Error|Exception $e){
            var_dump($root);
            throw new ParseError($e);
        }
        return $out;
    }

    private function _build(Node $node, Node $parent=null) {
        //handling of comments , directives, tags should be here
         // if ($n->type === NT::COMMENT && !self::INCLUDE_COMMENTS) {continue;}
        $value = $node->value;
        // var_dump($node->serialize());
        if (!($value instanceof Node) && !($value instanceof \SplQueue)) {
            return $node->getPhpValue();
        }else {
            $children = $value;
            if ($children instanceof Node) {
                $children = new SplQueue();
                $children->enqueue($value);
            }
            $children->rewind();
            $reference = $node->type;
            if ($node->type === NT::ROOT) {
                $children = $this->_removeUnbuildable($children);
                $reference = $children->current()->type;
            }
            switch($reference) {
                case NT::LITTERAL:        return $this->_litteral($children);
                case NT::LITTERAL_FOLDED: return $this->_litteral($children, true);
                case NT::MAPPING:         return $this->_map($node);
                case NT::SEQUENCE:        return $this->_seq($children);
                default: return $this->_build($value);
            }
        }
    }

    private function _litteral(SplQueue $children, $folded = false):string
    {
        try{
            $output = '';
            for ($children->rewind(); $children->valid() ; $children->next()) { 
                $output .= $children->current()->value.($folded ? " " : PHP_EOL);
            }
        }catch(Error $err) {
                  echo "catched: ", $err->getMessage(), PHP_EOL;
            // throw new Exception("catched: ", $err->getMessage(), PHP_EOL);
        }
        return $output;
    }

    //EVOLUTION:  if keyname contains unauthorized character for PHP property name : replace with '_'  ???
    private function _map(Node $node):StdClass {
        $out = new \StdClass;
        foreach ($node->value as $key => $child) {
            if (in_array($child->type, [NT::KEY, NT::MAPPING])) {
                if (!property_exists($child, "name")) {
                    throw new \Exception("YamlLoader: in MAPPING ${NT::getName($child->type)} has NO NAME on line $child->line for '$this->filePath'");
                }
                $out->{$child->name} = $this->_build($child);           }
            }else{
                $this->build($child, $node);
            }
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


    private function _removeUnbuildable(SplQueue $children) {
        $out = new \SplQueue;
        for ($children->rewind();  $children->valid(); $children->next()) { 
            if(!in_array($children->current()->type, NT::$NOTBUILDABLE)){
                $out->enqueue($children->current());
            } 
        }
        $out->rewind();
        return $out;
    }

    //TODO : make it more robust by a diff of DOC_START and DOC_END
    // determines if there are  :
    // - mulitple documents  -> ROOT = array of docs
    // _ OR just one         -> ROOT = map|seq|litteral|scalar
    // private function _defineRoot(Node $root) {
    //     $childrenList = $root->children;
    //     $childrenTypes = array_map(function ($n) {return $n->type;}, $root->children);
    //     $out = [];
    //     $pos = array_search(NT::DOC_END, $childrenTypes);
    //     $r = new Node();
    //     while ($pos !== false && $pos !== 0) {
    //         $n = new Node();
    //         $n->type = NT::DOCUMENT;
    //         $n->children = $this->_removeUnbuildable(array_splice($childrenList, $pos));
    //         $r->children[] = $n;
    //         $childrenTypes = array_slice($childrenTypes, $pos + 1);
    //         $pos = array_search(NT::DOC_END, $childrenTypes);
    //     }
    //     if (property_exists($r, 'children') && count($childrenList) > 0) {
    //         $r->children[] = $this->_removeUnbuildable($childrenList);
    //     } else {
    //         $root->children = $this->_removeUnbuildable($childrenList);
    //     }
    //     return property_exists($r, 'children') ? $r : $root;
    // }

    public function checkChildrenCoherence(SplQueue $children)
    {
        $types = [];
        foreach ($children as $key => $child) {
             $types[] = $child->type;
        }
        return array_unique($types, SORT_NUMERIC) > 1;
    }
}
/**
 * the return Object representing a YAML file content
 */
class YamlObject extends ArrayObject
{
    private $_references = [];
    private $_comments   = [];
    private $_documents  = [];
    function __construct(argument)
    {
        # code...
    }

    public function getReference($referenceName = null)
    {
        if (array_key_exists($referenceName, $this->_references)) {
            return $this->_references[$referenceName];
        }
        return $this->_references; 
    }

    public function getComment($lineNumber = null)
    {
        if (array_key_exists($lineNumber, $this->_comments)) {
            return $this->_comments[$lineNumber];
        }
        return $this->_comments;   
    }

    public function getDocument($identifier = null)
    {
        if (array_key_exists($identifier, $this->_documents)) {
            return $this->_documents[$identifier];
        }
        return count($this->_documents)===1 ? $this->_documents[0] : $this->_documents;
    }
}