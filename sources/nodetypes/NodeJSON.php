<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeJSON extends Node
{
    public function __construct(string $nodeString, int $line, $json)
    {
        parent::__construct($nodeString, $line);
        $this->value = $json;
    }

    public function isAwaitingChildren()
    {
        return false;
    }

    public function getValue(&$parent = null)
    {
        return $this->value;
    }
}