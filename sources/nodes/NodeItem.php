<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeItem extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match(Regex::ITEM, ltrim($nodeString), $matches);
        $value = isset($matches[1]) ? ltrim($matches[1]) : null;
        if (!empty($value)) {
            $n = NodeFactory::get($value, $line);
            $n->indent = $this->indent + 2;
            $this->add($n);
        }
    }

    public function add(Node $child):Node
    {
        $value = $this->value;
        if ($value instanceof NodeKey && $child instanceof NodeKey) {
            if ($value->indent === $child->indent) {
                return parent::add($child);
            } elseif ($value->isAwaitingChild($child)){
                return $value->add($child);
            } else {
                throw new \ParseError('key ('.$value->identifier.')@'.$value->line.' has already a value', 1);
            }
        }
        return parent::add($child);
    }

    public function getTargetOnEqualIndent(Node &$node):Node
    {
        $supposedParent = $this->getParent();
        if ($node->indent === $supposedParent->indent) {
            return $supposedParent->getParent();
        }
        return $supposedParent;
    }

    public function getTargetOnMoreIndent(Node &$node):Node
    {
        return !is_null($this->value) && $this->value->isAwaitingChild($node) ? $this->value : $this;
    }

    /**
     * Builds an item. Adds the item value to the parent array|Iterator
     *
     * @param array|\Iterator|null $parent The parent
     *
     * @throws \Exception  if parent is another type than array or object Iterator
     * @return null
     */
    public function build(&$parent = null)
    {
        if (!is_null($parent) && !is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an ArrayIterator not ".
                (is_object($parent) ? get_class($parent) : gettype($parent)));
        }
        if (is_null($parent)) {
            return [$this->value->build()];
        } else {
            $ref = is_array($parent) ? $parent : iterator_to_array($parent);
            $numKeys = array_keys($ref);
            $key = count($numKeys) > 0 ? max($numKeys) + 1 : 0;
            $parent[$key] = $this->value->build();
        }
    }

    public function isAwaitingChild(Node $node):bool
    {
        if (is_null($this->value)) {
            return true;
        } elseif ($this->value instanceof NodeSetKey && $node instanceof NodeSetValue) {
            return true;
        } else {
            return $this->getDeepestNode()->isAwaitingChild($node);
        }
    }
}