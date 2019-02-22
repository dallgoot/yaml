<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeScalar extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $value = trim($nodeString);
        if ($value !== '') {
            $hasComment = strpos($value, ' #');
            if (!is_bool($hasComment)) {
                    $realValue    = trim(substr($value, 0, $hasComment));
                    $commentValue = trim(substr($value, $hasComment));
                    $realNode = NodeFactory::get($realValue, $line);
                    $realNode->indent = null;
                    $commentNode = NodeFactory::get($commentValue, $line);
                    $commentNode->indent = null;
                    $this->add($realNode);
                    $this->add($commentNode);
            }
        }
    }

    public function build(&$parent = null)
    {
        return is_null($this->value) ? Builder::getScalar(trim($this->raw)) : $this->value->build();
    }

    // public function add(Node $child):Node
    // {
    //     return $this->parent ? $this->parent->add($child) : parent::add($child);
    // }

    public function getTargetOnLessIndent(Node &$previous):Node
    {
        if ($previous instanceof NodeScalar || $previous instanceof NodeBlank ) {
            return $previous->getParent();
        } else {
            return parent::getTargetOnLessIndent($previous);
        }
    }

    public function getTargetOnMoreIndent(Node &$previous):Node
    {
        if ($previous instanceof NodeScalar || $previous instanceof NodeBlank ) {
            return $previous->getParent();
        } else {
            return parent::getTargetOnMoreIndent($previous);
        }
    }
}