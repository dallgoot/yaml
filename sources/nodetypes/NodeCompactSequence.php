<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeCompactSequence extends Node
{
    public function __constructor(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $this->value = new NodeList();
        preg_match_all(Regex::SEQUENCE_VALUES, trim(substr(ltrim($nodeString), 1,-1)), $matches);
        foreach ($matches['item'] as $key => $item) {
            $this->value->push(new NodeItem('- '.$item, $line));
        }
    }

    public function build(&$parent = null)
    {
        $out = $parent ?? [];
        $tmp = $this->value instanceof Node ? new NodeList($this->value) : $this->value;
        foreach ($tmp as $child) {
            $child->build($out);
        }
        return new Compact($out);
    }
}