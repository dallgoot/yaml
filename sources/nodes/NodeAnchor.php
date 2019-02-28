<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeAnchor extends NodeActions
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

    public function isAwaitingChild(Node $node):bool
    {
        return is_null($this->value);
    }
}