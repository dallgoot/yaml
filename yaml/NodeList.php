<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;

/**
 * TODO
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 */
class NodeList extends \SplDoublyLinkedList
{
    /* @var null|int */
    public $type = null;//Y::LITT_FOLDED;

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
     * @return integer  The &-sum of all the types.
     */
    public function getTypes():int
    {
        $types = 0;
        foreach ($this as $child) {
            $types &= $child->type;
        }
        return $types;
    }
}
