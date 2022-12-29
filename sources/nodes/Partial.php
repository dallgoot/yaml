<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Partial extends NodeGeneric
{
    /**
     * What first character to determine if escaped sequence are allowed
     *
     * @param      NodeGeneric     $current     The current
     * @param      array    $emptyLines  The empty lines
     *
     * @return     boolean  true to skip normal Loader process, false to continue
     */
    public function specialProcess(NodeGeneric &$current, array &$emptyLines): bool
    {
        $parent = $this->getParent();
        $addValue = ltrim($current->raw);
        $separator = ' ';
        if ($this->raw[-1] === ' ' || $this->raw[-1] === "\n") {
            $separator = '';
        }
        if ($current instanceof Blank) {
            $addValue = "\n";
            $separator = '';
        }
        $node = NodeFactory::get($this->raw . $separator . $addValue, $this->line);
        $node->indent = null;
        $parent->value = null;
        $parent->add($node);
        return true;
    }

    public function build(&$parent = null)
    {
        throw new \ParseError("Partial value found at line $this->line", 1);
    }
}
