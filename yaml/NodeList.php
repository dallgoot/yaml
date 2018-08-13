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
    public $type = null;//Y\LITT_FOLDED;

    public function __construct()
    {
        $this->setIteratorMode(NodeList::IT_MODE_KEEP);
    }

    // public function __debugInfo():array
    // {
    //     return ['type' => Y::getName($this->type), "dllist" => $this->dllist];
    // }

    // public static function __set_state($an_array)
    // {
    //     $o = new stdClass;
    //     $o->type = Y::getName($this->type);
    //     return $o;
    // }

    public function getTypes():int
    {
        $types = 0;
        foreach ($this as $child) {
            $types &= $child->type;
        }
        return $types;
    }
}
