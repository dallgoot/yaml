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
class SetValue extends NodeGeneric
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $v = substr(ltrim($nodeString), 1);
        if (!empty($v)) {
            $value = NodeFactory::get($v, $line);
            $value->indent = null;
            $this->add($value);
        }
    }

    /**
     * Builds a set value.
     *
     * @param object $parent The parent (the document object or any previous object created through a mapping key)
     */
    public function build(&$parent = null)
    {
        $prop = array_keys(get_object_vars((object) $parent));
        $key = end($prop);
        $parent->{$key} = is_null($this->value) ? null : $this->value->build();
        return null;
    }

    public function isAwaitingChild(NodeGeneric $node): bool
    {
        return is_null($this->value) || $this->getDeepestNode()->isAwaitingChild($node);
    }
}
