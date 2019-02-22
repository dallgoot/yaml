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
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $this->value = trim($nodeString);
    }

    public function build(&$parent = null)
    {
        return substr(trim($this->value), 1,-1);
    }

    //     public function getTargetOnMoreIndent(Node &$previous):Node
    // {
    //     if ($previous instanceof NodeScalar || $previous instanceof NodeBlank || $previous instanceof NodeQuoted) {
    //         return $previous->getParent();
    //     } else {
    //         return parent::getTargetOnMoreIndent($previous);
    //     }
    // }
}