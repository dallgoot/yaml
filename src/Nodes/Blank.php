<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Generic\Literals;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Blank extends NodeGeneric
{
    public function add(NodeGeneric $child): NodeGeneric
    {
        if ($this->_parent instanceof NodeGeneric) {
            return $this->_parent->add($child);
        } else {
            throw new \ParseError(__METHOD__ . " no parent to add to", 1);
        }
    }

    public function specialProcess(NodeGeneric &$previous, array &$emptyLines): bool
    {
        $deepest = $previous->getDeepestNode();
        if ($previous instanceof Scalar) {
            $emptyLines[] = $this->setParent($previous->getParent());
        } elseif ($deepest instanceof Literals) {
            $emptyLines[] = $this->setParent($deepest);
        }
        return true;
    }

    public function build(&$parent = null)
    {
        return "\n";
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node): ?NodeGeneric
    {
        return $this->getParent($node->indent);
    }

    public function getTargetOnMoreIndent(NodeGeneric &$node): ?NodeGeneric
    {
        return $this->getParent($node->indent);
    }
}
