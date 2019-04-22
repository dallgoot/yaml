<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class NodeCompactSequence extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match_all(Regex::SEQUENCE_VALUES, trim(substr(trim($nodeString), 1,-1)), $matches);
        foreach ($matches['item'] as $key => $item) {
            $i = new NodeItem('', $line);
            $i->indent = null;
            $itemValue = NodeFactory::get(trim($item));
            $itemValue->indent = null;
            $i->add($itemValue);
            $this->add($i);
        }
    }

    public function build(&$parent = null)
    {
        if (is_null($this->value)) {
            return null;
        }
        if ($this->value instanceof Node) {
            $this->value = new NodeList($this->value);
            $this->value->type = NodeList::SEQUENCE;
        }
        return new Compact($this->value->build());
    }
}