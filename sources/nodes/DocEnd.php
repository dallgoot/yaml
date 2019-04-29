<?php

namespace Dallgoot\Yaml\Nodes;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class DocEnd extends NodeGeneric
{
    public function build(&$parent = null)
    {
        // Does nothing
        return null;
    }
}