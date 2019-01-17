<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y, Regex as R};

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class NodeHandlers
{

    /**
     * Set $node type and value when $nodevalue starts with a quote (simple or double)
     *
     * @param string $nodeValue The node value
     * @param Node   $node      The node
     *
     * @see   Node::identify
     */
    public static function onQuoted(string $nodeValue, Node $node)
    {
        $node->type = R::isProperlyQuoted(trim($nodeValue)) ? Y::QUOTED : Y::PARTIAL;
        $node->value = trim($nodeValue);
    }

    /**
     * Set $node type and value when NodeValue starts with a Set characters "?:"
     *
     * @param string $nodeValue The node value
     * @param Node   $node      The node
     *
     * @see   Node::identify
     */
    public static function onSetElement(string $nodeValue, Node $node)
    {
        $node->type = $nodeValue[0] === '?' ? Y::SET_KEY : Y::SET_VALUE;
        $v = trim(substr($nodeValue, 1));
        if (!empty($v)) {
            $node->value = new NodeList(new Node($v, $node->line));
        }
    }

    /**
     * Process when a "key: value" syntax is found in the parsed string.
     * Sets $node type and value, and add child if applicable.
     * Note : key is match 1, value is match 2 as per regex from R::KEY
     *
     * @param array $matches The matches provided by 'preg_match' function in Node::parse
     * @param Node  $node    The current Node being created
     *
     * @return null
     * @see    Node::identify
     */
    public static function onKey(array $matches, Node $node)
    {
        $node->type = Y::KEY;
        $node->identifier = trim($matches[1], '"\' ');
        $value = isset($matches[2]) ? trim($matches[2]) : null;//var_dump($value);
        if (!empty($value)) {
            $hasComment = strpos($value, ' #');
            if (is_bool($hasComment) || R::isProperlyQuoted($value)) {
                $n = new Node(trim($value), $node->line);
                $n->indent = $node->indent + strlen($node->identifier);
                $node->add($n);
            } else {
                $n = new Node(trim(substr($value, 0, $hasComment)), $node->line);
                $n->indent = $node->indent + strlen($node->identifier);
                $node->add($n);
                if ($n->type !== Y::PARTIAL) {
                    $comment = new Node(trim(substr($value, $hasComment)), $node->line);
                    $comment->identifier = true; //to specify it is NOT a fullline comment
                    $node->add($comment);
                }
            }
        }
    }

    /**
     * Determines the Node type and value when a compact object/array syntax is found
     *
     * @param string $value The value assumed to start with { or [ or characters
     * @param Node   $node  The current Node being created
     *
     * @return null
     * @see    Node::identify
     */
    public static function onCompact(string $value, Node $node)
    {
        $node->value = json_decode($value, false, 512, JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if (json_last_error() === JSON_ERROR_NONE) {
            $node->type = Y::JSON;
            return;
        }
        $node->value = new NodeList();
        if (preg_match(R::MAPPING, $value)) {
            $node->type = Y::COMPACT_MAPPING;
            $node->value->type = Y::COMPACT_MAPPING;
            preg_match_all(R::MAPPING_VALUES, trim(substr($value, 1,-1)), $matches);
            foreach ($matches['k'] as $index => $property) {
                $n = new Node('', $node->line);
                $n->type = Y::KEY;
                $n->identifier = trim($property, '"\' ');//TODO : maybe check for proper quoting first ?
                $n->value = new Node(trim($matches['v'][$index]), $node->line);
                $node->value->push($n);
            }
            return;
        }
        if (preg_match(R::SEQUENCE, $value)) {
            $node->type = Y::COMPACT_SEQUENCE;
            $node->value->type = Y::COMPACT_SEQUENCE;
            preg_match_all(R::SEQUENCE_VALUES, trim(substr($value, 1,-1)), $matches);
            foreach ($matches['item'] as $key => $item) {
                $i = new Node('', $node->line);
                $i->type = Y::ITEM;
                $i->add(new Node(trim($item), $node->line));
                $node->value->push($i);
            }
            return;
        }
        $node->value = $value;
        $node->type  = Y::PARTIAL;
    }

    /**
     * Determines Node type and value when an hyphen "-" is found
     *
     * @param string $nodeValue The node string value
     * @param Node   $node      The current Node being created
     *
     * @return null
     * @see    Node::identify
     */
    public static function onHyphen(string $nodeValue, Node $node)
    {
        if (substr($nodeValue, 0, 3) === '---') {
            $node->type = Y::DOC_START;
            $rest = trim(substr($nodeValue, 3));
            if (!empty($rest)) {
                $n = new Node($rest, $node->line);
                $n->indent = $node->indent + 4;
                $node->add($n);
            }
        } elseif (preg_match(R::ITEM, $nodeValue, $matches)) {
            $node->type = Y::ITEM;
            if (isset($matches[1]) && !empty(trim($matches[1]))) {
                $n = new Node(trim($matches[1]), $node->line);
                $n->indent = $node->indent + 2;
                $node->add($n);
            }
        } else {
            $node->type  = Y::SCALAR;
            $node->value = $nodeValue;
        }
    }

    /**
     * Sets Node type and value according to $nodeValue when one of these characters is found : !,&,*
     *
     * @param string $nodeValue The node value
     * @param Node   $node      The current Node being created
     *
     * @return null
     * @see    Node::identify
     * @todo   handle tags like  <tag:clarkevans.com,2002:invoice>
     */
    public static function onNodeAction(string $nodeValue, Node $node)
    {
        $v = substr($nodeValue, 1);
        $node->type = ['!' => Y::TAG, '&' => Y::REF_DEF, '*' => Y::REF_CALL][$nodeValue[0]];
        $node->identifier = $v;
        $pos = strpos($v, ' ');
        if (is_int($pos)) {
            $node->identifier = strstr($v, ' ', true);
            $value = trim(substr($nodeValue, $pos + 1));
            $value = R::isProperlyQuoted($value) ? trim($value, "\"'") : $value;
            $node->add(new Node($value, $node->line));
        }
    }

}