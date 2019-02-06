<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodePartial extends Node
{

    public function specialProcess(Node &$previous, array &$emptyLines)
    {
        $deepest = $previous->getDeepestNode();
        //what first character to determine if escaped sequence are allowed
        $val = ($this->value[-1] !== "\n" ? ' ' : '').substr($this->raw, $this->indent);
        $this->getParent()->value = NodeFactory::get($this->value.$val, $deepest->line);
        return true;
    }
}