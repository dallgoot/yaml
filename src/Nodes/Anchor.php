<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\Nodes\Generic\Actions;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Anchor extends Actions
{
    public function &build(&$parent = null)
    {
        $name = substr((string) $this->anchor, 1);
        $yamlObject = $this->getRoot()->getYamlObject();
        if (str_starts_with((string) $this->anchor, "*")) {
            try {
                return $yamlObject->getReference($name);
            } catch (\Throwable $e) {
                throw new \ParseError("Unknown anchor : '$name' this:" . $this->anchor, 1, $e);
            }
        } else {
            $built = is_null($this->value) ? null : $this->value->build($parent);
            return $yamlObject->addReference($name, $built);
        }
    }

    public function isAwaitingChild(NodeGeneric $node): bool
    {
        return is_null($this->value);
    }
}
