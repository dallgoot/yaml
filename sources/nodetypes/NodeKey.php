<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeKey extends Node
{
    const ERROR_NO_KEYNAME = self::class.": key has NO IDENTIFIER on line %d";

    public function __construct(string $nodeString, int $line, array $matches)
    {
        parent::__construct($nodeString, $line);
        $this->setIdentifier(trim($matches[1], '"\' '));
        $value = isset($matches[2]) ? trim($matches[2]) : null;
        if (!empty($value)) {
            $child = NodeFactory::get($value, $line);
            $child->indent = null;
            $this->add($child);
        }
    }

    public function setIdentifier(string $keyString)
    {
        if ($keyString === '') {
           throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $this->line));
        } else {
            $keyNode = NodeFactory::get($keyString);
            if (!is_null($keyNode->_anchor)) {
                $this->_anchor = $keyNode->_anchor;
                $this->identifier = trim($keyNode->value->raw);
            } elseif (!is_null($keyNode->_tag)) {
                $this->_tag = $keyNode->_tag;
                $this->identifier = trim($keyNode->value->raw);
            } elseif ($keyNode instanceof NodeScalar) {
                $this->identifier = trim($keyNode->raw);//$keyNode->build();
            }
        }
    }

    public function add(Node $child):Node
    {
        if ($this->value instanceof Node && isOneOf($this->getDeepestNode(), ['NodeLit','NodeLitFolded'])) {
                return $this->getDeepestNode()->add($child);
        } else {
            if (is_null($this->value) && ($child instanceof NodeKey || $child instanceof NodeItem) ) {
                $this->value = new NodeList;
            }
            return parent::add($child);
        }
    }

    // public function getTargetOnLessIndent(Node &$previous):Node
    // {
    //     if ($this->indent === 0) {
    //         return $previous->getRoot();
    //     } else {
    //         return parent::getTargetOnLessIndent($previous);
    //     }
    // }

    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous Node
     *
     * @return Node
     */
    public function getTargetOnEqualIndent(Node &$previous):Node
    {
        if ($this->indent === 0) {
            return $previous->getRoot();
        } else {
            return parent::getTargetOnEqualIndent($previous);
        }
    }

    public function getTargetOnMoreIndent(Node &$previous):Node
    {
        if ($previous instanceof NodeItem) {
            if (is_null($previous->value)) {
                return $previous;
            } else {
                $deepest = $previous->getDeepestNode();
                if ($deepest instanceof NodeKey || $deepest instanceof NodeItem) {
                    return $deepest;
                }
            }
            return $previous->getRoot();
        } else {
            return parent::getTargetOnMoreIndent($previous);
        }
    }


    public function isAwaitingChildren():bool
    {
        return is_null($this->value)
                || ($this->value instanceof NodeComment)
                || ($this->value instanceof NodeScalar);
    }

    /**
     * Builds a key and set the property + value to the given parent
     *
     * @param object|array $parent The parent
     *
     * @throws \ParseError if Key has no name(identifier) Note: empty string is allowed
     * @return null
     */
    public function build(&$parent = null)
    {
        if (is_null($this->value)) {
            $result = null;
        } elseif (isOneOf($this->value, ['NodeKey', 'NodeItem'])) {
            $tmp = new NodeList($this->value);
            $result = $tmp->build();
        } else {
            $result = $this->value->build();
        }
        if (is_array($parent)) {
            $parent[$this->identifier] = $result;
        } else {
            $parent->{$this->identifier} = $result;
        }
}

}