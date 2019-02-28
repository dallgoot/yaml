<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeDirective extends Node
{
    /**
     * Builds a directive. ---NOT IMPLEMENTED YET---
     *
     * @todo implement if required
     */
    public function build(&$parent = null)
    {
        if (is_null($this->value)) {
            return null;
        } else {
            return $this->value->build($parent);
        }
    }

    public function add(Node $child):Node
    {
        return $this->getRoot()->add($child);
    }
}