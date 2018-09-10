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
     */
    public function __construct()
    {
        $this->setIteratorMode(NodeList::IT_MODE_KEEP);
    }

    /**
     * Gets the types of the elements in this NodeList
     *
     * @return integer The &-sum of all the types.
     */
    public function getTypes():int
    {
        $types = 0;
        foreach ($this as $child) {
            $types |= $child->type;
        }
        return $types;
    }

    /**
     * Provides a slimmer output when using var_dump Note: currently PHP ignores it on SPL types
     */
    public function __debugInfo()
    {
        return ['type'=> Y::getName($this->type), 'dllist'=> $this->dllist];
    }
}
