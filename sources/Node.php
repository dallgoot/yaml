<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
abstract class Node
{
    /** @var null|string|boolean */
    protected $identifier;
    /** @var null|Node */
    protected $_parent;

    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var null|string */
    public $raw;
    /** @var null|Node|NodeList */
    public $value;
    /** @var string|null */
    public $anchor;
    /** @var string|null */
    public $tag;

    /**
     * Create the Node object and parses $nodeString
     *
     * @param string|null $nodeString The node string
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
     * @param Node $node The node
     *
     * @return Node|self The currentNode
     */
    protected function setParent(Node $node):Node
    {
        $this->_parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $_parent
     *
     * @param int|null $indent The indent
     *
     * @return Node   The parent.
     */
    public function getParent(int $indent = null):Node
    {
        if (!is_int($indent)){
            if ($this->_parent instanceof Node) {
                return $this->_parent;
            } else {
                throw new \Exception("Cannnot find a parent for ".get_class($this), 1);
            }
        }
        $cursor = $this->getParent();
        while (!($cursor instanceof NodeRoot)
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

    protected function getRoot():Node
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
     * - if value is a NodeList, push $child into
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

   /**
     * Find parent target when current Node indentation is lesser than previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnLessIndent(Node &$node):Node
    {
        $supposedParent = $this->getParent($node->indent);
        if ($node instanceof NodeItem && $supposedParent instanceof NodeRoot) {
            if ($supposedParent->value->has('NodeKey')) {
                $lastKey = null;
                foreach ($supposedParent->value as $key => $child) {
                    if ($child instanceof NodeKey) {
                        $lastKey = $child;
                    }
                }
                return $lastKey;
            }
        }
        return $supposedParent;
    }

    /**
     * Find parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnEqualIndent(Node &$node):Node
    {
        return $this->getParent();
    }

   /**
     * Find parent target when current Node indentation is superior than previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnMoreIndent(Node &$node):Node
    {
        return $this->isAwaitingChild($node) ? $this : $this->getParent();
    }

    protected function isAwaitingChild(Node $node):bool
    {
        return false;
    }

    /**
     *
     * @param Array|Object|null         $parent The parent collector or NULL otherwise
     *
     * @return mixed  whatever the build process returns
     */
    abstract function build(&$parent = null);

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
