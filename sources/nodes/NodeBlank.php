<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeBlank extends Node
{
    public function add(Node $child):Node
    {
        return $this->_parent->add($child);
    }

    public function specialProcess(Node &$previous, array &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        //what first character to determine if escaped sequence are allowed
        //if this is empty $separator depends on previous last character (escape slash)
        $separator = ' ';
        if ($previous instanceof NodeScalar) {
            $emptyLines[] = $this->setParent($previous->getParent());
        } elseif ($deepest instanceof NodeLiterals) {
            $emptyLines[] = $this->setParent($deepest);
        }
        return true;
    }

    public function build(&$parent = null)
    {
        return "\n";
    }

    public function getTargetOnEqualIndent(Node &$node):Node
    {
        return $this->getParent($node->indent);
    }

    public function getTargetOnMoreIndent(Node &$node):Node
    {
        return $this->getParent($node->indent);
    }
}