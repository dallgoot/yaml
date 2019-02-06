<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeSequence extends Node
{
    public function build(&$parent = null)
    {
        $out = $parent ?? [];
        $tmp = $this->value instanceof Node ? new NodeList($this->value) : $this->value;
        foreach ($tmp as $child) {
            $child->build($out);
        }
        return $out;
    }
}