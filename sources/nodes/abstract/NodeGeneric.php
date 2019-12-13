<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;

/**
 * An abstract type for all Nodes that defines generic behaviour
 * Node deriving from this MUST implement the "build" method
 *
 * Note: custom var_dump output is defined by method "__debugInfo"
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
abstract class NodeGeneric
{
    /** @var null|string|boolean */
    public $identifier;
    /** @var null|NodeGeneric */
    protected $_parent;

    /** @var int|null */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var string */
    public $raw = '';
    /** @var null|NodeGeneric|NodeList */
    public $value;
    /** @var string|null */
    public $anchor;
    /** @var string|null */
    public $tag;

    /**
     * Create the Node object and parses $nodeString
     *
     * @param string $nodeString The node string
     * @param int|null    $line       The line
     * @todo make it more permissive to tabs but replacement
     */
    public function __construct(string $nodeString, $line = 0)
    {
        $this->raw    = $nodeString;
        $this->line   = (int) $line;
        $nodeValue    = preg_replace("/^\t+/m", " ", $nodeString);
        $this->indent = strspn($nodeValue, ' ');
    }

    /**
     * Sets the parent of the current Node
     *
     * @param NodeGeneric $node The node
     *
     * @return NodeGeneric The currentNode
     */
    protected function setParent($node):NodeGeneric
    {
        $this->_parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $_parent
     *
     * @param int|null $indent The indent
     *
     * @return NodeGeneric   The parent.
     */
    public function getParent(int $indent = null):NodeGeneric
    {
        if (!is_int($indent)){
            if ($this->_parent instanceof NodeGeneric) {
                return $this->_parent;
            } else {
                throw new \Exception("Cannnot find a parent for ".get_class($this), 1);
            }
        }
        $cursor = $this->getParent();
        while (!($cursor instanceof Root)
                && (is_null($cursor->indent)
                || $cursor->indent >= $indent)) {
            if ($cursor->_parent) {
                $cursor = $cursor->_parent;
            } else {
                break;
            }
        }
        return $cursor;
    }

    /**
     * Gets the root of the structure map (or current Yaml document)
     *
     * @throws     \Exception  (description)
     *
     * @return     Root   The root node.
     */
    protected function getRoot():Root
    {
        if (is_null($this->_parent)) {
            throw new \Exception(__METHOD__.": can only be used when Node has a parent set", 1);
        }
        $pointer = $this;
        do {
            if ($pointer->_parent instanceof NodeGeneric) {
                $pointer = $pointer->_parent;
            } else {
                throw new \Exception("Node has no _parent set : ".get_class($pointer), 1);
            }
        } while (!($pointer instanceof Root));
        return $pointer;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, push $child into
     *
     * @param NodeGeneric $child The child
     *
     * @return NodeGeneric
     */
    public function add(NodeGeneric $child):NodeGeneric
    {
        $child->setParent($this);
        if (is_null($this->value)) {
            $this->value = $child;
        } else {
            if ($this->value instanceof NodeGeneric) {
                $this->value = new NodeList($this->value);
            }
            $this->value->push($child);
        }
        return $child;
    }

    /**
     * Gets the deepest node.
     *
     * @return NodeGeneric  The deepest node.
     */
    public function getDeepestNode():NodeGeneric
    {
        $cursor = $this;
        while ($cursor->value instanceof NodeGeneric) {
            $cursor = $cursor->value;
        }
        return $cursor;
    }

    public function specialProcess(/** @scrutinizer ignore-unused */ NodeGeneric &$previous, /** @scrutinizer ignore-unused */ array &$emptyLines):bool
    {
        return false;
    }

   /**
     * Find parent target when current Node indentation is lesser than previous node indentation
     *
     * @param NodeGeneric $node
     *
     * @return NodeGeneric|Key
     */
    public function getTargetOnLessIndent(NodeGeneric &$node):NodeGeneric
    {
        $supposedParent = $this->getParent($node->indent);
        if ($node instanceof Item && $supposedParent instanceof Root) {
            if ($supposedParent->value->has('Key')) {
                $supposedParent->value->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO);
                foreach ($supposedParent->value as $child) {
                    if ($child instanceof Key) {
                        $supposedParent->value->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);
                        // $supposedParent->value->rewind();
                        return $child;
                    }
                }
            }
        }
        return $supposedParent;
    }

    /**
     * Find parent target when current Node indentation is equal to previous node indentation
     *
     * @param NodeGeneric $node
     *
     * @return NodeGeneric
     */
    public function getTargetOnEqualIndent(NodeGeneric &$node):NodeGeneric
    {
        return $this->getParent();
    }

   /**
     * Find parent target when current Node indentation is superior than previous node indentation
     *
     * @param NodeGeneric $node
     *
     * @return NodeGeneric
     */
    public function getTargetOnMoreIndent(NodeGeneric &$node):NodeGeneric
    {
        return $this->isAwaitingChild($node) ? $this : $this->getParent();
    }

    protected function isAwaitingChild(NodeGeneric $node):bool
    {
        return false;
    }

    /**
     *
     * @param Array|Object|null         $parent The parent collector or NULL otherwise
     *
     * @return mixed  whatever the build process returns
     */
    public abstract function build(&$parent = null);

    /**
     * Determines if $subject is one of the Node types provided (as strings) in $comparison array
     * A node type is one of the class found in "nodes" folder.
     *
     * @param  string    ...$classNameList  A list of string whre each is a Node type e.g. 'Key', 'Blank', etc.
     *
     * @return     boolean  True if $subject is one of $comparison, False otherwise.
     */
    public function isOneOf(...$classNameList):bool
    {
        foreach ($classNameList as $className) {
            $fqn = __NAMESPACE__."\\$className";
            if ($this instanceof $fqn) return true;
        }
        return false;
    }

    /**
     * PHP internal function for debugging purpose : simplify output provided by 'var_dump'
     *
     * @return array  the Node properties and respective values displayed by 'var_dump'
     */
    public function __debugInfo():array
    {
        $props = [];
        $props['line->indent'] = "$this->line -> $this->indent";
        if ($this->identifier) $props['identifier'] = "($this->identifier)";
        if ($this->anchor)     $props['anchor']     = "($this->anchor)";
        if ($this->tag)        $props['tag']        = "($this->tag)";
        if ($this->value)      $props['value']      = $this->value;
        // $props['value'] = $this->value;
        $props['raw']   = $this->raw;
        if (!$this->_parent)  $props['parent'] = 'NO PARENT!!!';
        return $props;
    }
}
