<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\YamlObject;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Root extends NodeGeneric
{
    /** @var null|YamlObject */
    private $_yamlObject;
    /** @var NodeList */
    public $value;

    public function __construct()
    {
        $this->value = new NodeList();
    }

    public function getParent(int $indent = null, $type = 0):NodeGeneric
    {
        if ($this->_parent !== null) {
            throw new \ParseError(__CLASS__." can NOT have a parent, something's wrong", 1);
        }
        return $this;
    }

    public function getRoot():Root
    {
        return $this;
    }

    public function getYamlObject():YamlObject
    {
        if ($this->_yamlObject) {
            return $this->_yamlObject;
        }
        throw new \Exception("YamlObject has not been set yet", 1);
    }

    public function build(&$parent = null)
    {
        return $this->buildFinal($parent);
    }

    private function buildFinal(YamlObject $yamlObject):YamlObject
    {
        $this->_yamlObject = $yamlObject;
        $this->value->setIteratorMode(NodeList::IT_MODE_DELETE);
        // $this->value->rewind();
        foreach ($this->value as $key => $child) {
            $child->build($yamlObject);
        }
        return $yamlObject;
    }
}