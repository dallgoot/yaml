<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Generic\Literals;
use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Regex;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Key extends NodeGeneric
{
    const ERROR_NO_KEYNAME = self::class . ": key has NO IDENTIFIER on line %d";

    public function __construct(string $nodeString, int $line, ?array $matches = null)
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
            $child = NodeFactory::getKeyValue($value, $line);
            $child->indent = null;
            $this->add($child);
        }
    }

    public function setIdentifier(string $keyString): void
    {
        if ($keyString === '') {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $this->line));
        } else {
            $node = NodeFactory::get($keyString);
            if ($node->isOneOf('Tag', 'Quoted')) {
                $built = $node->build();
                if (is_object($built)) {
                    $this->identifier = $built->value;
                } else {
                    $this->identifier = (string) $node->build();
                }
            } elseif ($node instanceof Scalar) {
                $this->identifier = trim($node->raw);
            }
        }
    }

    public function add(NodeGeneric $child): NodeGeneric
    {
        if ($this->value instanceof NodeGeneric && $this->value->isOneOf('Literal', 'LiteralFolded', 'Anchor')) {
            return $this->value->add($child);
        } else {
            return parent::add($child);
        }
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node): NodeGeneric
    {
        if ($node instanceof Item) {
            return $this;
        }
        return $this->getParent();
    }

    public function getTargetOnMoreIndent(NodeGeneric &$node): NodeGeneric
    {
        if (!is_null($this->value)) {
            if ($this->getDeepestNode()->isAwaitingChild($node)) {
                return $this->getDeepestNode();
            }
        }
        return $this;
    }


    public function isAwaitingChild(NodeGeneric $node): bool
    {
        if (is_null($this->value) || $node instanceof Comment) {
            return true;
        } elseif ($this->value instanceof NodeGeneric) {
            $current = $this->value;
        } else {
            $current = $this->value->current();
        }
        if ($current instanceof Comment) {
            return true;
        }
        if ($current instanceof Scalar) {
            return $node->isOneOf('Scalar', 'Blank');
        }
        if ($current->isOneOf('Key', 'Item')) {
            $cClass = get_class($current);
            return $node instanceof $cClass;
        }
        if ($current instanceof Literals) {
            return $node->indent > $this->indent;
        }
        if ($current instanceof Anchor) {
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
     */
    public function build(&$parent = null): ?object
    {
        if ($this->value instanceof Anchor) {
            $result = &$this->value->build();
        } else {
            $result = is_null($this->value) ? null : $this->value->build();
        }
        if (is_null($parent)) {
            $parent = new \stdClass;
            $parent->{$this->identifier} = &$result;
            return $parent;
        } else {
            $parent->{$this->identifier} = &$result;
            return null;
        }
    }
}
