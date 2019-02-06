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
        preg_match(Regex::ITEM, $nodeString, $matches);
        if (isset($matches[1]) && !empty(trim($matches[1]))) {
            // $n->indent = $indent + 2;
            $n = NodeFactory::get($matches[1], $line);
            $this->add($n);
        }
    }
    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetonEqualIndent(Node &$previous):Node
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest->isAwaitingChildren()) {
            return $deepest;
        } else {
            return parent::getTargetonEqualIndent($previous);
        }
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
        // extract((array) $node, EXTR_REFS);
        if (!is_array($parent) && !($parent instanceof \ArrayIterator)) {
            throw new \Exception("parent must be an ArrayIterator not ".(is_object($parent) ? get_class($parent) : gettype($parent)));
        }
        $ref = $parent instanceof \ArrayIterator ? $parent->getArrayCopy() : $parent;
        $numKeys = array_filter(array_keys($ref), 'is_int');
        $key = count($numKeys) > 0 ? max($numKeys) + 1 : 0;
        if ($this->value instanceof NodeKey) {
            $this->value->build($parent);
            return;
        }
        $parent[$key] = $this->value->build();
    }

}