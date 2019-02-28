<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeActions extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $trimmed = ltrim($nodeString);
        $pos = strpos($trimmed, ' ');
        $name = $trimmed;
        if (is_int($pos)) {
            $name = strstr($trimmed, ' ', true);
            $value = trim(substr($trimmed, $pos + 1));
            if ($value !== '') {
                $child = NodeFactory::get($value, $line);
                $child->indent = null;
                $this->add($child);
            }
        }
        if ($this instanceof NodeTag) {
            $this->_tag = $name;
        } else {
            $this->_anchor = $name;
        }
    }

    public function build(&$parent = null)
    {
        // Nothing to do here
    }
}