<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeDocStart extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $rest = trim(substr($nodeString, 3));
        if (!empty($rest)) {
            // $n->indent = $indent + 4;
            $this->add(NodeFactory::get($rest, $line));
        }
    }

    // public function build(&$parent = NULL)
    // {
        // if (is_scalar($child->value)) {
        //     $parent->setText(Node2PHP::get($child));
        // } elseif ($child->value instanceof NodeTag){
        //     $parent->addTag($child->value->identifier);
        // } else {
        //     $parent->setText(self::build($child->value));
        // }
    // }
}