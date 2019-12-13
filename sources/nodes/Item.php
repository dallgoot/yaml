<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\YamlObject;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Item extends NodeGeneric
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

    public function add(NodeGeneric $child):NodeGeneric
    {
        $value = $this->value;
        if ($value instanceof Key && $child instanceof Key) {
            if ($value->indent === $child->indent) {
                return parent::add($child);
            } elseif ($value->isAwaitingChild($child)){
                return $value->add($child);
            } else {
                // throw new \ParseError('key ('.$value->identifier.')@'.$value->line.' has already a value', 1);
                throw new \ParseError('key @'.$value->line.' has already a value', 1);
            }
        }
        return parent::add($child);
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node):NodeGeneric
    {
        $supposedParent = $this->getParent();
        if ($node->indent === $supposedParent->indent) {
            return $supposedParent->getParent();
        }
        return $supposedParent;
    }

    public function getTargetOnMoreIndent(NodeGeneric &$node):NodeGeneric
    {
        return $this->value instanceof NodeGeneric && $this->value->isAwaitingChild($node) ? $this->value : $this;
    }

    /**
     * Builds an item. Adds the item value to the parent array|Iterator
     *
     * @param array|YamlObject|null $parent The parent
     *
     * @throws \Exception  if parent is another type than array or object Iterator
     * @return null|array
     */
    public function build(&$parent = null)
    {
        if (!is_null($parent) && !is_array($parent) && !($parent instanceof YamlObject)) {
            throw new \Exception("parent must be an array or YamlObject not ".
                (is_object($parent) ? get_class($parent) : gettype($parent)));
        }
        $value = $this->value ? $this->value->build() : null;
        if (is_null($parent)) {
            return [$value];
        } else {
            // $ref = is_array($parent) ? $parent : iterator_to_array($parent);
            // $numKeys = array_keys($ref);
            // $key = count($numKeys) > 0 ? max($numKeys) + 1 : 0;
            // $parent[$key] = $value;
            $parent[] = $value;
        }
    }

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        if (is_null($this->value)) {
            return true;
        } elseif ($this->value instanceof SetKey && $node instanceof SetValue) {
            return true;
        } else {
            return $this->getDeepestNode()->isAwaitingChild($node);
        }
    }
}