<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodePartial extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $this->value = ltrim($nodeString);
    }

    /**
     * What first character to determine if escaped sequence are allowed
     *
     * @param      Node     $current     The current
     * @param      array    $emptyLines  The empty lines
     *
     * @return     boolean  true to skip normal Loader process, false to continue
     */
    public function specialProcess(Node &$current, array &$emptyLines):bool
    {
        $parent = $this->getParent();
        $addValue = ltrim($current->raw);
        $separator = ' ';
        if ($this->raw[-1] === ' ' || $this->raw[-1] === "\n") {
            $separator = '';
        }
        if ($current instanceof NodeBlank) {
            $addValue = "\n";
            $separator = '';
        }
        $node = NodeFactory::get($this->raw.$separator.$addValue, $this->line);
        $node->indent = null;
        $parent->value = null;
        $parent->add($node);
        return true;
    }

    public function build(&$parent = null)
    {
        throw new \ParseError("Partial value found at line $this->line", 1);
    }
}