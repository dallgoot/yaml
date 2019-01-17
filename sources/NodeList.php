<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Yaml as Y;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeList extends \SplDoublyLinkedList
{
    /* @var null|int */
    public $type = null;

    /**
     * NodeList constructor
     * 
     * @param Node $node (optional) a node that will be pushed as first element
     */
    public function __construct(Node $node = null)
    {
        $this->setIteratorMode(NodeList::IT_MODE_KEEP);
        if (!is_null($node)) {
            $this->push($node);
        }
    }

    /**
     * Gets the types of the elements in this NodeList
     *
     * @return integer The "|-sum" of all the types.
     */
    public function getTypes():int
    {
        $types = 0;
        foreach ($this as $child) {
            if ($child->type & Y::DOC_START) {//var_dump(__METHOD__.' theres a DOCSTART');
                if ($child->value instanceof Node) {
                    $types |= $child->value->type;
                } elseif ($child->value instanceof NodeList) {
                    $child->value->forceType();
                    $types |= $child->value->type;
                    // $types |= $child->value->getTypes();
                }
            } else {
                $types |= $child->type;
            }
        }
        $this->rewind();
        return $types;
    }

    /**
     * If no type is set for this NodeList, forces a type according to its children types
     *
     * @return self
     * @throws \ParseError  (description)
     */
    public function forceType()
    {
        if (is_null($this->type)) {
            $childTypes = $this->getTypes();
            if ($childTypes & (Y::KEY|Y::SET_KEY)) {
                if ($childTypes & Y::ITEM) {
                    throw new \ParseError(self::class.": Error conflicting types found");
                }
                $this->type = Y::MAPPING;
            } else {
                if ($childTypes & Y::ITEM) {
                    $this->type = Y::SEQUENCE;
                } elseif ($childTypes & Y::DOC_START && $this->count() === 1) {//var_dump(__METHOD__.' theres a DOCSTART');
                    if ($child->value instanceof Node) {
                        $this->type = $child->value->type;
                    } elseif ($child->value instanceof NodeList) {
                        $child->value->forceType();
                        $this->type =  $child->value->type;
                    }
                } elseif (!($childTypes & Y::COMMENT)) {
                    $this->type = Y::LITT_FOLDED;
                } else {
                    $this->type = Y::SCALAR;
                }
            }
        }
        return $this;
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
