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
                $this->identifier = trim($keyNode->raw);
            }
        }
    }

    public function add(Node $child):Node
    {
        if ($this->value instanceof Node && isOneOf($this->value, ['NodeLit','NodeLitFolded', 'NodeAnchor'])) {
            return $this->value->add($child);
        } else {
            return parent::add($child);
        }
    }

    public function getTargetOnEqualIndent(Node &$node):Node
    {
        if ($node instanceof NodeItem) {
            return $this;
        }
        return $this->getParent();
    }

    public function getTargetOnMoreIndent(Node &$node):Node
    {
        if (!is_null($this->value)) {
            if ($this->getDeepestNode()->isAwaitingChild($node)) {
                return $this->getDeepestNode();
            }
        }
        return $this;
    }


    public function isAwaitingChild(Node $node):bool
    {
        if (is_null($this->value) || $node instanceof NodeComment) {
            return true;
        }
        $current = $this->value instanceof Node ? $this->value : $this->value->current();
        if ($current instanceof NodeComment) {
            return true;
        }
        if($current instanceof NodeScalar) {
            return isOneOf($node, ['NodeScalar', 'NodeBlank']);
        }
        if ($current instanceof NodeItem) {
            return $node instanceof NodeItem;
        }
        if ($current instanceof NodeKey) {
            return $node instanceof NodeKey;
        }
        if ($current instanceof NodeLiterals) {
            return $node->indent > $this->indent;
        }
        if ($current instanceof NodeAnchor) {
            return $current->isAwaitingChild($node);
        }
        return false;
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
        if (!is_null($this->_tag)) {
            return TagFactory::transform($this->_tag, $this)->build($parent);
        }
        $result = is_null($this->value) ? null : $this->value->build();
        if (is_null($parent)) {
            $parent = new \StdClass;
            $parent->{$this->identifier} = $result;
            return $parent;
        } else {
            $parent->{$this->identifier} = $result;
        }
    }
}