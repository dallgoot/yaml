<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class JSON extends NodeGeneric
{
    private const JSON_OPTIONS = \JSON_PARTIAL_OUTPUT_ON_ERROR | \JSON_UNESCAPED_SLASHES;

    public function build(&$parent = null)
    {
        return json_decode($this->raw, false, 512, self::JSON_OPTIONS);
    }
}
