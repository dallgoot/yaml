<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Node
{
    /** @var null|string|boolean */
    public $identifier;
    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var null|string */
    public $raw;
    /** @var null|Node|NodeList */
    public $value;

    /** @var string|null */
    public $_anchor;
    /** @var null|Node */
    protected $_parent;
    /** @var string|null */
    public $_tag;

    /**
     * Create the Node object and parses $nodeString IF not null (else assume a root type Node)
     *
     * @param string|null $nodeString The node string
     * @param int|null    $line       The line
     * @todo make it more permissive to tabs but replacement
     */
    public function __construct(string $nodeString = null, $line = 0)
    {
        $this->raw = $nodeString;
        $this->line = (int) $line;
        $nodeValue = preg_replace("/^\t+/m", " ", $nodeString);
        $this->indent = strspn($nodeValue, ' ');
    }

    /**
     * Sets the parent of the current Node
     *
     * @param Node $node The node
     *
     * @return Node|self The currentNode
     */
    public function setParent(Node $node):Node
    {
        $this->_parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $parent OR the current Node itself
     *
     * @param int|null $indent The indent
     *
     * @return Node   The parent.
     */
    public function getParent(int $indent = null):Node
    {
        if (!is_int($indent)) return $this->_parent;
        $cursor = $this->getParent();
        while (!($cursor instanceof NodeRoot)
                && (is_null($cursor->indent)
                                || $cursor->indent >= $indent)) {
            $cursor = $cursor->_parent;
        }
        return $cursor;
    }

    public function getRoot():Node
    {
        if (is_null($this->_parent)) {
            throw new \Exception(__METHOD__.": can only be used when Node has a parent set", 1);
        }
        $cursor = $this;
        while (!($cursor instanceof NodeRoot) && $cursor->_parent instanceof Node) {
            $cursor = $cursor->_parent;
        }
        return $cursor;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, push $child into and set NodeList type accordingly
     *
     * @param Node $child The child
     *
     * @return Node
     */
    public function add(Node $child):Node
    {
        $child->setParent($this);
        if (is_null($this->value)) {
            $this->value = $child;
        } else {
            if ($this->value instanceof Node) {
                $this->value = new NodeList($this->value);
            }
            $this->value->push($child);
        }
        return $child;
    }

    /**
     * Gets the deepest node.
     *
     * @return Node|self  The deepest node.
     */
    public function getDeepestNode():Node
    {
        $cursor = $this;
        while ($cursor->value instanceof Node) {
            $cursor = $cursor->value;
        }
        return $cursor;
    }

    public function specialProcess(Node &$previous, array &$emptyLines):bool
    {
        return false;
    }

    public function getTargetOnLessIndent(Node &$previous):Node
    {
        $candidate = $previous->getParent($this->indent);
        // if ($this instanceof NodeItem) {
        // var_dump(get_class($candidate).$candidate->identifier.$this->indent );
        // }
        return $candidate;
    }

    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnEqualIndent(Node &$previous):Node
    {
        // if ($this instanceof NodeKey) {
        //     # code...
        // var_dump(get_class($this), get_class($previous->getParent()));
        // }
        return $previous->getParent();
    }

   /**
     * Modify parent target when current Node indentation is superior to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnMoreIndent(Node &$previous):Node
    {
        // return $previous->getParent($this->indent)->getDeepestNode();
        // return $previous->getParent($this->indent);
        // if ($previous->value instanceof ) {
        //     # code...
        // }
        return $previous;//->getParent();
    }

    public function isAwaitingChildren():bool
    {
        return true;
    }

    /**
     * Generic function to distinguish between Node and NodeList
     *
     * @param mixed         $parent The parent
     *
     * @return mixed  ( description_of_the_return_value )
     */
    public function build(&$parent = null)
    {
        if (!is_null($this->_tag)) {
            if (TagFactory::isKnown($this->_tag)) {
                return TagFactory::transform($this->_tag, $value)->build($parent);
            } else {
                // TODO : this workds for nodeItem or NodeKey
                return new Tag($this->_tag, $this->value->build($parent));
            }
        }
        if (!is_null($this->_anchor)) {
            $yamlObject = $this->getRoot()->getYamlObject();
            if ($this instanceof NodeAnchor){
                return $this->build($parent);//$yamlObject->getReference(substr($this->_anchor, 1));
            } else {
                $yamlObject->addReference(substr($this->_anchor, 1), $this);
                return $this;
            }
        }
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
        if ($this->_anchor)    $props['_anchor']    = "($this->_anchor)";
        if ($this->_tag)       $props['_tag']       = "($this->_tag)";
        $props['value'] = $this->value;
        $props['raw']   = $this->raw;
        if ($this->_parent)  $props['parent'] = get_class($this->_parent);
        return $props;
    }
}
