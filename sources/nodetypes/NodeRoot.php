<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeRoot extends Node
{
    public function __construct()
    {
        $this->value = new NodeList();
        $this->value->setIteratorMode(NodeList::IT_MODE_DELETE);
    }

    public function getParent(int $indent = null, $type = 0):Node
    {
        return $this;
    }

    public function getValue(&$parent = null)
    {
        return $this->value;
    }
}