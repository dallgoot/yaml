<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Compact;
use Dallgoot\Yaml\Regex;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class CompactMapping extends NodeGeneric
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match_all(Regex::MAPPING_VALUES, trim(substr(trim($nodeString), 1,-1)), $matches);
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
        if ($this->value instanceof NodeGeneric) {
            $this->value = new NodeList($this->value);
            $this->value->type = NodeList::MAPPING;
        }
        $obj = (object) $this->value->build();
        return new Compact($obj);
    }
}