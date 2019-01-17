<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y, Regex as R};

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Node
{
    /** @var null|string|boolean */
    public $identifier;
    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var null|string */
    public $raw;
    /** @var int */
    public $type;
    /** @var null|Node|NodeList|string */
    public $value;

    /** @var null|Node */
    private $parent;

    /**
     * Create the Node object and parses $nodeString IF not null (else assume a root type Node)
     *
     * @param string|null $nodeString The node string
     * @param int|null    $line       The line
     */
    public function __construct($nodeString = null, $line = 0)
    {
        $this->line = (int) $line;
        if (is_null($nodeString)) {
            $this->type = Y::ROOT;
        } else {
            $this->raw = $nodeString;
            $this->parse($nodeString);
        }
    }

    /**
     * Sets the parent of the current Node
     *
     * @param Node $node The node
     *
     * @return Node|self The currentNode
     */
    public function setParent(Node $node):Node
    {
        $this->parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $parent OR the current Node itself
     *
     * @param int|null $indent The indent
     * @param int $type  first ancestor of this YAML::type is returned
     *
     * @return Node|null   The parent.
     */
    public function getParent(int $indent = null, $type = 0):Node
    {
        if ($this->type === Y::ROOT) return $this;
        if (!is_int($indent)) return $this->parent ?? $this;
        $cursor = $this;
        while ($cursor instanceof Node && $cursor->indent >= $indent) {
            if ($cursor->indent === $indent && $cursor->type !== $type) {
                $cursor = $cursor->parent ?? $cursor;
                break;
            }
            $cursor = $cursor->parent;
        }
        return $cursor ?? $this;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, push $child into and set NodeList type accordingly
     *
     * @param Node $child The child
     * 
     * @todo  refine the conditions when Y::LITTERALS
     */
    public function add(Node $child)
    {
        if ($this->type & (Y::SCALAR|Y::QUOTED)) {
            $this->getParent()->add($child);
            return;
        }
        $child->setParent($this);
        if (is_null($this->value)) {
            $this->value = $child;
            return;
        } elseif (is_scalar($this->value)) {
            $this->value = new Node($this->value, $this->line);
        }
        if ($this->value instanceof Node) {
            if ($this->value->type & Y::LITTERALS) {
                $type = $this->value->type;
                $this->value = new NodeList();
                $this->value->type = $type;
            } else {
                $this->value = new NodeList($this->value);
            }
        }
        $this->value->push($child);
    }

    /**
     * Gets the deepest node.
     *
     * @return Node|self  The deepest node.
     */
    public function getDeepestNode():Node
    {
        $cursor = $this;
        while ($cursor->value instanceof Node || $cursor->value instanceof NodeList) {
            if ($cursor->value instanceof NodeList) {
                if ($cursor->value->count() === 1) {
                    $cursor = $cursor->value->OffsetGet(0);
                } else {
                    $cursor = $cursor;
                    break;
                }
            } else {
                $cursor = $cursor->value;
            }
        }
        return $cursor;
    }

    /**
     * Parses the string (assumed to be a line from a valid YAML)
     *
     * @param string $nodeString The node string
     *
     * @return Node|self
     */
    public function parse(string $nodeString):Node
    {
        $nodeValue = preg_replace("/^\t+/m", " ", $nodeString); //permissive to tabs but replacement
        $this->indent = strspn($nodeValue, ' ');
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = Y::BLANK;
        } elseif (substr($nodeValue, 0, 3) === '...') {//TODO: can have something on same line ?
            $this->type = Y::DOC_END;
        } elseif (preg_match(R::KEY, $nodeValue, $matches)) {
            NodeHandlers::onKey($matches, $this);
        } else {
            $this->identify($nodeValue);
        }
        return $this;
    }

    /**
     *  Set the type and value according to first character
     *
     * @param string $nodeValue The node value
     */
    private function identify($nodeValue)
    {
        $v = ltrim(substr($nodeValue, 1));
        $first = $nodeValue[0];
        if ($first === "-")                        NodeHandlers::onHyphen($nodeValue, $this);
        elseif (in_array($first, ['"', "'"]))      NodeHandlers::onQuoted($nodeValue, $this);
        elseif (in_array($first, ['{', '[']))      NodeHandlers::onCompact($nodeValue, $this);
        elseif (in_array($first, ['?', ':']))      NodeHandlers::onSetElement($nodeValue, $this);
        elseif (in_array($first, ['!', '&', '*'])) NodeHandlers::onNodeAction($nodeValue, $this);
        else {
            $characters = [ '#' =>  [Y::COMMENT, $nodeValue],
                            '%' =>  [Y::DIRECTIVE, $nodeValue],
                            '>' =>  [Y::LITT_FOLDED, null],
                            '|' =>  [Y::LITT, null]
                            ];
            if (isset($characters[$first])) {
                $this->type  = $characters[$first][0];
                $this->value = $characters[$first][1];
            } else {
                $this->type  = Y::SCALAR;
                $this->value = $nodeValue;
            }
        }
    }

    /**
     * PHP internal function for debugging purpose : simplify output provided by 'var_dump'
     *
     * @return array  the Node properties and respective values displayed by 'var_dump'
     */
    public function __debugInfo():array
    {
        return ['line'  => $this->line,
                'indent'=> $this->indent,
                'type'  => Y::getName($this->type).($this->identifier ? "($this->identifier)" : ''),
                'value' => $this->value,
                'raw'   => $this->raw,
            ];
    }
}
