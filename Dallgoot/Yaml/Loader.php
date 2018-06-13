<?php
namespace Dallgoot\Yaml;
use Dallgoot\Yaml\Node as Node;
use Dallgoot\Yaml\Types as T;
use Dallgoot\Yaml\YamObject;

class Loader
{
    private $_content = NULL;
    private $filePath = NULL;
    private $_debug = true;
    const INCLUDE_DIRECTIVE = false;
    const INCLUDE_COMMENTS = true;

    const ERROR_NO_NAME = self::class.": in MAPPING %s has NO NAME on line %d for '%s'";
    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";
    //Exceptions
    const EXCEPTION_NO_FILE = self::class.": file '%s' does not exists (or path is incorrect?)";
    const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";

    public function __construct($absolutePath = null, $options = null) {
        /*TODO: handle options:
                    - include_directive
                    - include_comments
                    - debug
                    - dont Exception on parsing Errors
                    _ import date strings as dateTime Object
        */
        if (!is_null($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    public function load(String $absolutePath):Loader {
        $this->_debug && var_dump($absolutePath);
        $this->filePath = $absolutePath;
        if (!file_exists($absolutePath)) {
            throw new \Exception(sprintf(self::EXCEPTION_NO_FILE, $absolutePath));
        }
        $adle = "auto_detect_line_endings";
        $prevADLE = ini_get($adle);
        !$prevADLE && ini_set($adle, true);
        $content = file($absolutePath, FILE_IGNORE_NEW_LINES);
        !$prevADLE && ini_set($adle, false);
        if (is_bool($content)) {
            throw new \Exception(sprintf(self::EXCEPTION_READ_ERROR, $absolutePath));
        }
        $this->_content = $content;
        return $this;
    }

    public function parse($strContent = null) {
        $source = $strContent ? preg_split("/([^\n\r]+)/um", $strContent, null, PREG_SPLIT_DELIM_CAPTURE)
                                : $this->_content;
        //TODO : be more permissive on $strContent values
        if (!is_array($source)) {
            throw new \Exception('YamlLoader : content is not a string(maybe a file error?)');
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
            if (in_array($n->type, T::$LITTERALS)) {
                $deepestParent = $deepest->getParent();
                if ($deepest->type === T::EMPTY && 
                    $deepestParent->type === T::KEY) {
                    $deepestParent->value = $n;
                }else{
                    $deepest->value = $n;
                }
                continue;
            }
            if($n->type === T::EMPTY){
                if (in_array($deepest->type, T::$LITTERALS)) {
                    $n->setParent($deepest);
                    $emptyLines[] = $n;
                }else if($previous->type === T::STRING){
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
            if ($deepest->type === T::PARTIAL) {
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
                        case T::LITTERAL:
                        case T::LITTERAL_FOLDED:
                            $n->type = T::STRING;
                            $n->value = trim($lineString);
                            unset($n->name);
                            $parent = $deepest;
                            break;
                        case T::EMPTY:
                        case T::STRING:
                            if ($n->type === T::STRING) {
                                $deepest->type = T::STRING;
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
            $out = $this->_buildRoot($root);
        } catch (\Error|\Exception $e){
            var_dump($root);
            throw new \ParseError($e);
        }
        return $out;
    }

    private function _build(object $node, object $parent = null) {
        //handling of comments , directives, tags should be here
         // if ($n->type === T::COMMENT && !self::INCLUDE_COMMENTS) {continue;}
        $value = $node->value;
        // var_dump($node->serialize());
        if (!($value instanceof Node) && !($value instanceof \SplQueue)) {
            return $node->getPhpValue();
        }else {
            $children = $value;
            if ($children instanceof Node) {
                $children = new \SplQueue();
                $children->enqueue($value);
            }
            $children->rewind();
            $reference = $node->type;
            if ($node->type === T::ROOT) {
                $children = $this->_removeUnbuildable($children);
                $reference = $children->current()->type;
            }
            // var_dump($children);exit();
            switch($reference) {
                case T::LITTERAL:        return $this->_litteral($children);
                case T::LITTERAL_FOLDED: return $this->_litteral($children, true);
                case T::MAPPING:         return $this->_map($node);
                case T::SEQUENCE:        return $this->_seq($children);
                default: return $this->_build($value);
            }
        }
    }

    private function _litteral(\SplQueue $children, $folded = false):string
    {
        try{
            $output = '';
            for ($children->rewind(); $children->valid(); $children->next()) { 
                $output .= $children->current()->value.($folded ? " " : PHP_EOL);
            }
        }catch(\Error $err) {
                  echo "catched: ", $err->getMessage(), PHP_EOL;
            // throw new Exception("catched: ", $err->getMessage(), PHP_EOL);
        }
        return $output;
    }

    //EVOLUTION:  if keyname contains unauthorized character for PHP property name : replace with '_'  ???
    private function _map(Node $node):StdClass
    {
        $out = new \StdClass;
        foreach ($node->value as $key => $child) {
            if (in_array($child->type, [T::KEY, T::MAPPING])) {
                if (property_exists($child, "name")) {
                    $out->{$child->name} = $this->_build($child);
                }else{
                    $this->_error(sprintf(self::ERROR_NO_NAME, T::getName($child->type), $child->line, $this->filePath));
                }
            }else{            
                $this->_build($child, $node);
            }
        }
        return $out;
    }

    private function _seq(\SplQueue $children):array {
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


    private function _removeUnbuildable(\SplQueue $children) {
        $out = new \SplQueue;
        for ($children->rewind();  $children->valid(); $children->next()) { 
            if(!in_array($children->current()->type, T::$NOTBUILDABLE)){
                $out->enqueue($children->current());
            } 
        }
        $out->rewind();
        return $out;
    }

    private function _buildRoot(Node $node)
    {
        //check multiple documents & split if more than one documents
        $totalDocStart = 0;
        $documents = [];
        $node->value->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE); 
        foreach ($node->value as $key => $child) {
            if($child->type === T::DOC_START){
                $totalDocStart++;
            }
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0; 
            if(!array_key_exists($currentDoc, $documents)) $documents[$currentDoc] = new \SplQueue();                
            $documents[$currentDoc]->enqueue($child);
        }
// var_dump($documents);exit();
        //foreach documents
        $results = [];
        foreach ($documents as $key => $value) {
            $doc = new YamlObject();
            $childTypes = $this->_getChildrenTypes($value);
            $isMapping  = count(array_intersect($childTypes, [T::KEY, T::MAPPING])) > 0;
            $isSequence = in_array(T::ITEM, $childTypes);
            if ($isMapping && $isSequence) {
                $this->_error();
            }elseif ($isMapping) {
                $doc->type = T::MAPPING;
            }elseif ($isSequence) {
                $doc->type = T::SEQUENCE;
            }else{
                $doc->type = T::LITTERAL;
            }
            $doc->value = $value;
            $results[] = $this->_build($doc);
        }
        return $results;
    }

    private function _getChildrenTypes(\SplQueue $children)
    {
        $types = [];
        foreach ($children as $key => $child) {
            $types[] = $child->type;
        }
        return array_unique($types);
    }

    public function checkChildrenCoherence(\SplQueue $children)
    {
        $types = [];
        foreach ($children as $key => $child) {
             $types[] = $child->type;
        }
        return array_unique($types, SORT_NUMERIC) > 1;
    }

    public function _error($message)
    {
        if ($this->_options->noParsingException) {
            # code...
        }else{
            throw new \ParseError($message, 1);
        }
    }
}
