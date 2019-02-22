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
            // $n->indent = null;
            $this->add($n);
        }
    }

    public function add(Node $child):Node
    {
        // if ($this->value instanceof Node && $this->value->indent === 0 ) {
        if ($this->value instanceof NodeKey && $child instanceof NodeKey
            // && isOneOf($this->value, ['NodeLit','NodeLitFolded', 'NodeKey'])
            // && $child->indent !== $this->value->indent
            // && is_null($this->value->indent)
            // && $this->value->isAwaitingChildren()
            // && !($child instanceof NodeKey)
            ) {
            if ($child->indent > $this->value->indent) {
                return $this->value->add($child);
            }
        }
        // } else {
            // if (is_null($this->value) && ($child instanceof NodeKey || $child instanceof NodeItem) ) {
            //     $this->value = new NodeList;
            // }
            return parent::add($child);
        // }
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
        $deepest = $previous->getDeepestNode();
        if (!($previous instanceof Nodeitem) && $deepest->isAwaitingChildren()) {
            return $deepest;
        } else {
            return parent::getTargetOnEqualIndent($previous);
        }
    }

    public function getTargetOnLessIndent(Node &$previous):Node
    {
        $root = $previous->getRoot();
        if ($this->indent === 0) {
            // it's either the last key with indent=0 or Root
            if(!$root->value->has('NodeKey')) {
                return $root;
            } else {
                $lastKey = null;
                foreach ($root->value as $key => $child) {
                    if ($child instanceof NodeKey) {
                        $lastKey = $child;
                    }
                }
                return $lastKey;
            }
        }
        return parent::getTargetOnLessIndent($previous);
    }


    /**
     * Builds an item. Adds the item value to the parent array|Iterator
     *
     * @param array|\Iterator|null $parent The parent
     *
     * @throws \Exception  if parent is another type than array or object Iterator
     * @return null
     */
    public function build(&$parent = null):void
    {
        if (!is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an ArrayIterator not ".
                (is_object($parent) ? get_class($parent) : gettype($parent)));
        }
        // $ref = $parent instanceof \ArrayIterator ? iterator_to_array($parent) : $parent;
        // var_dump(__METHOD__.(is_object($parent) ? get_class($parent) : gettype($parent)), $parent);die();
        // $ref = is_array($parent) ? $parent : iterator_to_array($parent);
        if (is_array($parent)) {
            $ref = $parent;
        } else {
            $ref = count($parent) > 0 ? iterator_to_array($parent) : ['a' => 0];
        }
        $keys = array_keys($ref);
        $numKeys = count($keys) > 0 ? array_filter($keys, 'is_int') : $keys;
        $key = count($numKeys) > 0 ? max($numKeys) + 1 : 0;
        // if ($this->value instanceof NodeKey) {
        //     $this->value->build($parent);
        //     return;
        // }
        $null = null;
        $parent[$key] = $this->value->build($null);
    }

}