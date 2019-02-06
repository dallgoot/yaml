<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeQuoted extends Node
{
    public function __construct(string $nodeValue, $line)
    {
        $this->line  = $line;
        $this->value = trim($nodeValue);
    }

    public function getValue(&$parent = null)
    {
        return is_null($this->value) ? null : substr(trim((string) $this->value), 1, -1);
    }
}