<?php

namespace Dallgoot\Yaml\Nodes;


/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Directive extends NodeGeneric
{
    /**
     * Builds a directive. ---NOT IMPLEMENTED YET---
     *
     * @todo implement if required
     */
    public function build(&$parent = null)
    {
        // if (is_null($this->value)) {
            return null;
        // } else {
        //     return $this->value->build($parent);
        // }
    }

    public function add(NodeGeneric $child):NodeGeneric
    {
        return $child;
        // return $this->getRoot()->add($child);
    }
}