<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class NodeQuoted extends Node
{
    public function build(&$parent = null)
    {
        return substr(trim($this->raw), 1,-1);
    }
}