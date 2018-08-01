<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;

/**
 *
 */
class NodeList extends \SplDoublyLinkedList
{
    /* @var null|int */
    public $type;

    public function __construct()
    {
        //do nothing
    }

    public function __debugInfo():array
    {
        return ['type' => Y::getName($this->type) , "dllist" => $this->dllist];
    }
}
