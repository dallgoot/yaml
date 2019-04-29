<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;

/**
 * Common parent to NodeAnchor, NodeTag
 * Extract identifier (tag or anchor) and attach its value (another Node)
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
abstract class Actions extends NodeGeneric
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $trimmed = ltrim($nodeString);
        $pos = strpos($trimmed, ' ');
        $name = $trimmed;
        if (is_int($pos)) {
            $name = strstr($trimmed, ' ', true);
            $value = trim(substr($trimmed, $pos + 1));
            if ($value !== '') {
                $child = NodeFactory::get($value, $line);
                $child->indent = null;
                parent::add($child);
            }
        }
        if ($this instanceof Tag) {
            $this->tag = $name;
        } else {
            $this->anchor = $name;
        }
    }

    // public function build(&$parent = null)
    // {
    //     // Nothing to do here : on purpose  this method is overriden by children
    //     return null;
    // }
}