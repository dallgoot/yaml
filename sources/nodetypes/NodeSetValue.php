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
        $v = trim(substr($nodeString, 1));
        if (!empty($v)) {
            // $node->value = new NodeList(new Node($v, $node->line));
            $this->value = new NodeList(NodeFactory::get($v, $line));
        }
    }

    public function isAwaitingChildren()
    {
        return is_null($this->value);
    }


    /**
     * Builds a set value.
     *
     * @param Node   $node   The node of type YAML::SET_VALUE
     * @param object $parent The parent (the document object or any previous object created through a mapping key)
     */
    public static function buildSetValue(Node $node, &$parent = null)
    {
        $prop = array_keys(get_object_vars($parent));
        $key = end($prop);
        $value = $node->value;
        if ($value instanceof NodeItem) {
            $mother = new NodeSequence();
            $mother->add($value);
            $value = $mother;
        }
        if ($value instanceof NodeKey) {
            $mother = new NodeMapping();
            $mother->add($value);
            $value = $mother;
        }
        $parent->{$key} = is_null($value) ? null: $value->build($parent);
    }

}