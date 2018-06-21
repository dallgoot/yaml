<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Node as Node;
use Dallgoot\Yaml\Types as T;
use Dallgoot\Yaml\YamObject;

class Loader
{
    public $errors = [];
    private $_content;
    private $filePath;
    private $_debug   = 0;//TODO: determine levels
    private $_options = 0;
    //options
    public const EXCLUDE_DIRECTIVES = 0001;//DONT include_directive
    public const IGNORE_COMMENTS    = 0010;//DONT include_comments
    public const EXCEPTIONS_PARSING = 0100;//THROW Exception on parsing Errors
    public const NO_OBJECT_FOR_DATE = 1000;//DONT import date strings as dateTime Object
    //Errors
    const ERROR_NO_NAME    = self::class.": in MAPPING %s has NO NAME on line %d for '%s'";
    const INVALID_DOCUMENT = self::class.": DOCUMENT %d can NOT be a mapping AND a sequence";
    //Exceptions
    const EXCEPTION_NO_FILE    = self::class.": file '%s' does not exists (or path is incorrect?)";
    const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";

    public function __construct($absolutePath = null, $options = null, $debug = 0)
    {
        $this->_debug = is_int($debug) ? min($debug, 3) : 1;
        if (!is_null($options)) {
            $this->options = $options;
        }
        if (!is_null($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    public function load(String $absolutePath):Loader
    {
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

    public function parse($strContent = null)
    {
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
                } else {
                    $deepest->value = $n;
                }
                continue;
            }
            if ($n->type === T::EMPTY) {
                if (in_array($deepest->type, T::$LITTERALS)) {
                    $emptyLines[] = $n->setParent($deepest);
                } elseif ($previous->type === T::STRING) {
                    $emptyLines[] = $n->setParent($previous->getParent());
                }
                continue;
            } else {
                foreach ($emptyLines as $key => $node) {
                    $node->getParent()->add($node);
                }
                $emptyLines = [];
            }
            if ($deepest->type === T::PARTIAL) {
                $newValue = new Node($deepest->value.$lineString, $n->line);
                $mother = $deepest->getParent();
                $mother->value = $newValue->setParent($mother);
            } else {
                if ($n->indent === 0) {
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
        $this->_debug && var_dump("\033[33mParsed Structure\033[0m\n", $root);
        try {
            $out = $this->_buildFile($root);
        } catch (\Error|\Exception $e) {
            var_dump($root);
            throw new \ParseError($e);
        }
        return $out;
    }

    private function _build(object $node, $root = null, &$parent = null)
    {
        $method = $node instanceof \SplQueue ? "_buildQueue" : "_buildNode";
        return $this->{$method}($node, $root, $parent);
    }

    private function _buildQueue($node, $root, &$parent)
    {
        $type  = property_exists($node, "type") ? $node->type : $parent->type;
        if (is_object($parent) && $parent instanceof YamlObject) {
                $p = $parent;
        } else {
            switch ($type) {
                case T::MAPPING:  $p = new \StdClass();break;
                case T::SEQUENCE: $p = [];break;
                case T::LITTERAL:
                case T::LITTERAL_FOLDED: return $this->_litteral($node, $type);break;
            }
        }
        foreach ($node as $key => $child) {
            $this->_build($child, $root, $p);
        }
        return $p;
    }

    private function _buildNode($node, $root, &$parent)
    {
        $line  = property_exists($node, "line") ? $node->line : null;
        $name  = property_exists($node, "name") ? $node->name : null;
        $value = $node->value;
        $type  = $node->type;
        switch ($type) {
            case T::KEY: $this->_buildKey($value, $name, $type, $line, $root, $parent);
                        return;
            case T::ITEM: $this->_buildItem($value, $root, $parent);
                        return;
            case T::DIRECTIVE: return;//TODO
            case T::TAG:  return;//TODO
            case T::COMMENT: $root->addComment($line, $value); return;
            case T::REF_DEF:
            case T::REF_CALL:
                $tmp = is_object($value) ? $this->_build($value, $root, $parent) : $node->getPhpValue();
                $type === T::REF_DEF && $root->addReference($line, $name, $tmp);
                return $root->getReference($name);
            default:
                return is_object($value) ? $this->_build($value, $root, $parent) : $node->getPhpValue();
        }
    }

    private function _buildKey($value, $name, $type, $line, $root, &$parent)
    {
        if (is_null($name)) {
            $this->_error(sprintf(self::ERROR_NO_NAME, T::getName($type), $line, $this->filePath));
        } else {
            $parent->{$name} = $this->_build($value, $root, $parent->{$name});
        }
    }

    private function _buildItem($value, $root, &$parent)
    {
        if ($value instanceof Node && $value->type === T::KEY) {
            $parent[$value->name] = $this->_build($value, $root, $parent[$value->name]);
        } else {
            $c = count($parent);
            $parent[$c] = $this->_build($value, $root, $parent[$c]);
        }
    }

    /**
     * Builds a file.  check multiple documents & split if more than one documents
     *
     * @param      Node   $node   The root node
     *
     * @return     array  representing the total of documents in the file.
     */
    private function _buildFile(Node $node)
    {
        $totalDocStart = 0;
        $documents = [];
        $node->value->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
        foreach ($node->value as $key => $child) {
            if ($child->type === T::DOC_START) {
                $totalDocStart++;
            }
            //if 0 or 1 DOC_START = we are still in first document
            $currentDoc = $totalDocStart > 1 ? $totalDocStart - 1 : 0;
            if (!array_key_exists($currentDoc, $documents))
                $documents[$currentDoc] = new \SplQueue();
            $documents[$currentDoc]->enqueue($child);
        }
        $this->_debug >= 2 && var_dump($documents);
        $results = [];
        foreach ($documents as $key => $children) {
            $doc = new YamlObject();
            $childTypes = $this->_getChildrenTypes($children);
            $isMapping  = count(array_intersect($childTypes, [T::KEY, T::MAPPING])) > 0;
            $isSequence = in_array(T::ITEM, $childTypes);
            if ($isMapping && $isSequence) {
                $this->_error(sprintf(self::INVALID_DOCUMENT, $key));
            } elseif ($isSequence) {
                $children->type = T::SEQUENCE;
                // $doc->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            } else {
                $children->type = T::MAPPING;
                $doc->setFlags(\ArrayObject::STD_PROP_LIST);
            }
            $this->_debug >= 3 && var_dump($doc, $children);
            $results[] = $this->_build($children, $doc, $doc);
        }
        return $results;
    }

    private function _litteral(\SplQueue $children, $type):string
    {
        $folded = $type === T::LITTERAL_FOLDED ? " " : PHP_EOL;
        try {
            $output = '';
            foreach ($children as $key => $child) {
                $output .= $child->value.$folded;
            }
        } catch (\Error $err) {
            $this->error($err->getMessage());
        }
        return $output;
    }

    private function _removeUnbuildable(\SplQueue $children)
    {
        $out = new \SplQueue;
        foreach ($children as $key => $child) {
            if (!in_array($child->type, T::$NOTBUILDABLE)) {
                $out->enqueue($child);
            }
        }
        $out->rewind();
        return $out;
    }

    private function _getChildrenTypes(\SplQueue $children)
    {
        $types = [];
        foreach ($children as $key => $child) {
            $types[] = $child->type;
        }
        return array_unique($types);
    }

    public function _error($message)
    {
        if ($this->_options & self::EXCEPTIONS_PARSING) throw new \ParseError($message, 1);
        $this->errors[] = $message;
    }
}
