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
    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var int */
    public $type;
    /** @var null|string|boolean */
    public $identifier;
    /** @var Node|NodeList|null|string */
    public $value = null;
    /** @var null|string */
    public $raw;

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
     * @return Node|self   The parent.
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
        return $cursor;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, push $child into and set NodeList type accordingly
     *
     * @param Node $child The child
     * @todo  refine the conditions when Y::LITTERALS
     */
    public function add(Node $child)
    {
        if ($this->type & (Y::SCALAR|Y::QUOTED)) {
            $this->getParent()->add($child);
            return;
        }
        $child->setParent($this);
        $current = $this->value;
        if (is_null($current)) {
            $this->value = $child;
            return;
        }elseif ($current instanceof Node) {
            $this->value = new NodeList();
            if ($current->type & Y::LITTERALS) {
                $this->value->type = $current->type;
            } else {
                $this->value->push($current);
            }
        }
        // if (is_null($this->value->type)) {
        //     $this->adjustValueType($child);
        // }
        $this->value->push($child);
    }

    //modify value type according to child
    // private function adjustValueType(Node $child)
    // {
    //     if ($child->type & Y::SET_KEY)   $this->value->type = Y::SET;
    //     if ($child->type & Y::KEY)       $this->value->type = Y::MAPPING;
    //     if ($child->type & Y::ITEM)      $this->value->type = Y::SEQUENCE;
    // }

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
            $this->onKey($matches);
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
        if ($first === "-")                        $this->onHyphen($nodeValue);
        elseif (in_array($first, ['"', "'"]))      $this->onQuoted($nodeValue);
        elseif (in_array($first, ['{', '[']))      $this->onCompact($nodeValue);
        elseif (in_array($first, ['?', ':']))      $this->onSetElement($nodeValue);
        elseif (in_array($first, ['!', '&', '*'])) $this->onNodeAction($nodeValue);
        else {
            $characters = [ '#' =>  [Y::COMMENT, $v],
                            '%' =>  [Y::DIRECTIVE, $v],
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

    private function onQuoted($nodeValue)
    {
        $this->type = R::isProperlyQuoted($nodeValue) ? Y::QUOTED : Y::PARTIAL;
        $this->value = $nodeValue;
    }

    private function onSetElement($nodeValue)
    {
        $this->type = $nodeValue[0] === '?' ? Y::SET_KEY : Y::SET_VALUE;
        $v = trim(substr($nodeValue, 1));
        if (!empty($v)) {
            $this->value = new NodeList;
            $this->add(new Node($v, $this->line));
        }
    }

    /**
     * Process when a "key: value" syntax is found in the parsed string
     * Note : key is match 1, value is match 2 as per regex from R::KEY
     *
     * @param array $matches The matches provided by 'preg_match' function in Node::parse
     */
    private function onKey(array $matches)
    {
        $this->type = Y::KEY;
        $this->identifier = trim($matches[1], '"\' ');
        $value = $matches[2] ? trim($matches[2]) : null;
        if (!empty($value)) {
            $hasComment = strpos($value, ' #');
            if (is_bool($hasComment)) {
                $n = new Node($value, $this->line);
            } else {
                $n = new Node(trim(substr($value, 0, $hasComment)), $this->line);
                if ($n->type !== Y::PARTIAL) {
                    $comment = new Node(trim(substr($value, $hasComment + 1)), $this->line);
                    $comment->identifier = true; //to specify it is NOT a fullline comment
                    $this->add($comment);
                }
            }
            $n->indent = $this->indent + strlen($this->identifier);
            $this->add($n);
        }
    }

    /**
     * Determines the correct type and value when a compact object/array syntax is found
     *
     * @param string $value The value assumed to start with { or [ or characters
     *
     * @see Node::identify
     */
    private function onCompact($value)
    {
        $this->value = json_decode($value, false, 512, JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if (json_last_error() === JSON_ERROR_NONE){
            $this->type = Y::JSON;
            return;
        }
        $this->value = new NodeList();
        if (preg_match(R::MAPPING, $value)){
            $this->type = Y::COMPACT_MAPPING;
            $this->value->type = Y::COMPACT_MAPPING;
            preg_match_all(R::MAPPING_VALUES, trim(substr($value, 1,-1)), $matches);
            foreach ($matches['k'] as $index => $property) {
                $n = new Node('', $this->line);
                $n->type = Y::KEY;
                $n->identifier = trim($property, '"\' ');//TODO : maybe check for proper quoting first ?
                $n->value = new Node($matches['v'][$index], $this->line);
                $this->value->push($n);
            }
            return;
        }
        if (preg_match(R::SEQUENCE, $value)){
            $this->type = Y::COMPACT_SEQUENCE;
            $this->value->type = Y::COMPACT_SEQUENCE;
            $count = preg_match_all(R::SEQUENCE_VALUES, trim(substr($value, 1,-1)), $matches);
            foreach ($matches['item'] as $key => $item) {
                $i = new Node('', $this->line);
                $i->type = Y::ITEM;
                $i->add(new Node($item, $this->line));
                $this->value->push($i);
            }
            return;
        }
        $this->value = $value;
        $this->type  = Y::PARTIAL;
    }

    /**
     * Determines type and value when an hyphen "-" is found
     *
     * @param string $nodeValue The node value
     *
     * @see Node::identify
     */
    private function onHyphen($nodeValue)
    {
        if (substr($nodeValue, 0, 3) === '---') {
            $this->type = Y::DOC_START;
            $rest = trim(substr($nodeValue, 3));
            if (!empty($rest)) {
                $n = new Node($rest, $this->line);
                $n->indent = $this->indent + 4;
                $this->value = $n->setParent($this);
            }
        } elseif (preg_match(R::ITEM, $nodeValue, $matches)) {
            $this->type = Y::ITEM;
            if (isset($matches[1]) && !empty(trim($matches[1]))) {
                $n = new Node(trim($matches[1]), $this->line);
                $n->indent = $this->indent + 2;
                $this->value = $n->setParent($this);
            }
        } else {
            $this->type  = Y::SCALAR;
            $this->value = $nodeValue;
        }
    }

    /**
     * Determines the type and value according to $nodeValue when one of these characters is found : !,&,*
     *
     * @param string $nodeValue The node value
     *
     * @see  Node::identify
     * @todo handle tags like  <tag:clarkevans.com,2002:invoice>
     */
    private function onNodeAction($nodeValue)
    {
        $v = substr($nodeValue, 1);
        $this->type = ['!' => Y::TAG, '&' => Y::REF_DEF, '*' => Y::REF_CALL][$nodeValue[0]];
        $this->identifier = $v;
        $pos = strpos($v, ' ');
        if ($this->type & (Y::TAG|Y::REF_DEF) && is_int($pos)) {
            $this->identifier = strstr($v, ' ', true);
            $value = trim(substr($nodeValue, $pos + 1));
            $value = R::isProperlyQuoted($value) ? trim($value, "\"'") : $value;
            $this->add((new Node($value, $this->line))->setParent($this));
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
                'value' => $this->value];
    }
}
