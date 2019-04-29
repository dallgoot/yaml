<?php

namespace Dallgoot\Yaml\Nodes;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Anchor extends Actions
{
    public function build(&$parent = null)
    {
        $name = substr($this->anchor, 1);
        $yamlObject = $this->getRoot()->getYamlObject();
        if ($this->anchor[0] === "*") {
            return $yamlObject->getReference($name);
        } else {
            $built = $this->value->build($parent);
            $yamlObject->addReference($name, $built);
            return $built;
        }
    }

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        return is_null($this->value);
    }
}