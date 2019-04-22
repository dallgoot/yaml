<?php

namespace Dallgoot\Yaml;
use Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class NodeKey extends Node
{
    const ERROR_NO_KEYNAME = self::class.": key has NO IDENTIFIER on line %d";

    public function __construct(string $nodeString, int $line, array $matches = null)
    {
        parent::__construct($nodeString, $line);
        if (is_null($matches)) {
            if (!((bool) preg_match(Regex::KEY, ltrim($nodeString), $matches))) {
                throw new \ParseError("Not a KEY:VALUE syntax ($nodeString)", 1);
            }
        }
        $this->setIdentifier($matches[1]);
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
            if ($keyNode instanceof NodeTag || $keyNode instanceof NodeQuoted) {
                $this->identifier = $keyNode->build();
            } elseif ($keyNode instanceof NodeScalar) {
                $this->identifier = trim($keyNode->raw);
            }
            // if (!is_null($keyNode->anchor)) {
            //     $this->anchor = $keyNode->anchor;
            //     $anchor = $keyNode->anchor;
            //     $pos = strlen($keyNode->anchor);
            //     $this->identifier = $keyNode->value->raw;
            // } elseif (!is_null($keyNode->tag)) {
            //     $this->tag = $keyNode->tag;
            //     $raw = $keyNode->raw;
            //     $pos = strlen($keyNode->tag);
            //     $this->identifier = trim(substr($raw, $pos));
            // } elseif ($keyNode instanceof NodeScalar) {
            //     $this->identifier = ltrim($keyNode->raw);
            // }
        }
    }

    public function add(Node $child):Node
    {
        if ($this->value instanceof Node && Yaml::isOneOf($this->value, ['NodeLit','NodeLitFolded', 'NodeAnchor'])) {
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
            return Yaml::isOneOf($node, ['NodeScalar', 'NodeBlank']);
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
     * @return null|\StdClass
     */
    public function build(&$parent = null)
    {
        // if (!is_null($this->tag)) {
        //     return TagFactory::transform($this->tag, $this)->build($parent);
        // }
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