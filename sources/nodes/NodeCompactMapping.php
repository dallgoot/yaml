<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeCompactMapping extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match_all(Regex::MAPPING_VALUES, trim(substr(ltrim($nodeString), 1,-1)), $matches);
        foreach ($matches['k'] as $index => $property) {
            $pair = $property.': '.trim($matches['v'][$index]);
            $child = NodeFactory::get($pair, $line);
            $child->indent = null;
            $this->add($child);
        }
    }

    public function build(&$parent = null)
    {
        if (is_null($this->value)) {
            return null;
        }
        if ($this->value instanceof Node) {
            $this->value = new NodeList($this->value);
            $this->value->type = NodeList::MAPPING;
        }
        return new Compact($this->value->build());
    }
}