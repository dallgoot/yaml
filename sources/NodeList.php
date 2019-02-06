<?php
namespace Dallgoot\Yaml;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeList extends \SplDoublyLinkedList
{
    /**
     * NodeList constructor
     *
     * @param Node|null $node (optional) a node that will be pushed as first element
     */
    public function __construct(Node $node = null)
    {
        $this->setIteratorMode(NodeList::IT_MODE_KEEP);
        if (!is_null($node)) {
            $this->push($node);
        }
    }

    public function has(string $nodeType):bool
    {
        $tmp = clone $this;
        $tmp->rewind();
        foreach ($tmp as $child) {
            if (is_a($child, $nodeType)) return true;
        }
        return false;
    }

    public function hasContent():bool
    {
        $tmp = clone $this;
        $tmp->rewind();
        foreach ($tmp as $child) {
            if (!($child instanceof NodeComment)
                && !($child instanceof NodeDirective)
                && !($child instanceof NodeDocstart && is_null($child->value)) ) return true;
        }
        return false;
    }

    /**
     * Provides a slimmer output when using var_dump Note: currently PHP ignores it on SPL types
     * @todo activate when PHP supports it
     */
    // public function __debugInfo()
    // {
    //     return ['type'=> Y::getName($this->type), 'dllist'=> $this->dllist];
    // }
}
