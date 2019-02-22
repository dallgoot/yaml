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
        $rest = substr(ltrim($nodeString), 3);
        if (!empty($rest)) {
            $n = NodeFactory::get($rest, $line);
            $n->indent = null;
            $this->add($n);
        }
    }

    public function add(Node $child):Node
    {
        if ($this->value instanceof Node) {
            return $this->value->add($child);
        } else {
            return parent::add($child);
        }
    }

    public function build(&$parent = null)
    {
        if (is_null($parent)) {
            throw new Exception(__METHOD__." expects a YamlObject as parent", 1);
        }
        if (is_null($this->value)) {
            return null;
        } else {
            if ($this->value instanceof NodeTag){
                // $tagName =
                $parent->addTag($this->value->identifier);
                $this->value->build($parent);
            } else {
                $text = $this->value->build($parent);
                !is_null($text) && $parent->setText($text);
            }
        }
    }

    public function isAwaitingChildren():bool
    {
        return $this->value && isOneOf($this->value, ['NodeRefDef', 'NodeLit', 'NodeLitFolded']);
    }

    public function getTargetOnEqualIndent(Node &$previous):Node
    {
        return $previous->getRoot();
    }
}