<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Quoted extends NodeGeneric
{
    public function build(&$parent = null)
    {
        // return substr(Scalar::replaceSequences(trim($this->raw)), 1,-1);
        return (
            new Scalar('', 0))->replaceSequences(
                substr(trim($this->raw), 1, -1
                )
        );
    }
}
