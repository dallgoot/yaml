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
    public function __constructor(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match_all(Regex::MAPPING_VALUES, trim(substr(ltrim($nodeString), 1,-1)), $matches);
        foreach ($matches['k'] as $index => $property) {
            $pair = $property.': '.trim($matches['v'][$index]);
            // $fakeMatches = [0 => $property, 1 => trim($matches['v'][$index])];
            // $this->add(new NodeKey($pair, $line, $fakeMatches));
            $this->add(NodeFactory::get($pair, $line));
        }
    }

    public function build(&$parent = null)
    {
        $out = $parent ?? new \StdClass;
        $tmp = $this->value instanceof Node ? new NodeList($this->value) : $this->value;
        foreach ($tmp as $child) {
            $child->build($out);
        }
        return new Compact($out);
    }


}