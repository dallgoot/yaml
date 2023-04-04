<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Types\Compact;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class CompactSequence extends NodeGeneric
{
    public function __construct(string $nodeString, ?int $line)
    {
        parent::__construct($nodeString, $line);
        preg_match_all(Regex::SEQUENCE_VALUES, trim(substr(trim($nodeString), 1, -1)), $matches);
        foreach ($matches['item'] as $key => $item) {
            $i = new Item('', (int) $line);
            $i->indent = null;
            $itemValue = NodeFactory::get(trim($item));
            $itemValue->indent = null;
            $i->add($itemValue);
            $this->add($i);
        }
    }

    public function build(&$parent = null): ?Compact
    {
        if (is_null($this->value)) {
            return null;
        }
        if ($this->value instanceof NodeGeneric) {
            $this->value = new NodeList($this->value);
            $this->value->type = NodeList::SEQUENCE;
        }
        $arr = (array) $this->value->build();
        return new Compact($arr);
    }
}
