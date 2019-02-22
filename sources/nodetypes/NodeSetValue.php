<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeSetValue extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $v = substr(ltrim($nodeString), 1);
        if (!empty($v)) {
            $value = NodeFactory::get($v, $line);
            $value->indent = null;
            $this->value = $value;
        }
    }

    // public function isAwaitingChildren()
    // {
    //     return is_null($this->value);
    // }

    /**
     * Builds a set value.
     *
     * @param Node   $node   The node of type YAML::SET_VALUE
     * @param object $parent The parent (the document object or any previous object created through a mapping key)
     */
    public function build(&$parent = null)
    {
        $prop = array_keys(get_object_vars($parent));
        $key = end($prop);
        $value = $this->value;

        if ($value instanceof NodeItem || $value instanceof NodeKey) {
            $value = new NodeList($this->value);
        }
        $parent->{$key} = is_null($value) ? null: $value->build($parent);
    }

}