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
            try {
                // var_dump($yamlObject);exit();
                return $yamlObject->getReference($name);
            } catch (\Throwable $e) {
                throw new \ParseError("Unknown anchor : '$name' this:".$this->anchor,1,$e);
            }
        } else {
            $built = is_null($this->value) ? null : $this->value->build($parent);
            $yamlObject->addReference($name, $built);
            return $built;
        }
    }

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        return is_null($this->value);
    }
}