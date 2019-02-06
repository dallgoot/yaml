<?php

namespace Dallgoot\Yaml;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class NodeFactory
{
    private const JSON_OPTIONS = \JSON_PARTIAL_OUTPUT_ON_ERROR|\JSON_UNESCAPED_SLASHES;

    final public static function get($nodeString = null, $line = 0):Node
    {
        $nodeTrimmed = ltrim($nodeString);
        if ($nodeTrimmed === '') {
            return new NodeBlank($nodeString, $line);
        } elseif (substr($nodeTrimmed, 0, 3) === '...') {
            return new NodeDocEnd($nodeString, $line);
        } elseif (preg_match(Regex::KEY, $nodeTrimmed, $matches)) {
            return new NodeKey($nodeString, $line, $matches);
        } else {
            $first = $nodeTrimmed[0];
            $actions = ["-"   => 'onHyphen',
                        '>|'  => 'onLiteral',
                        '"\'' => 'onQuoted',
                        "#%"  => 'onSpecial',
                        "{["  => 'onCompact',
                        ":?"  => 'onSetElement',
                        '*&!' => 'onNodeAction'
                    ];
            foreach ($actions as $stringRef => $methodName) {
                if (is_int(strrpos($stringRef, $first))) {
                    return self::$methodName($nodeString, $line);
                }
            }
        }
        return new NodeScalar($nodeString, $line);
    }

    /**
     * Return the correct Node Object between NodeComment OR NodeDirective
     *
     * @param      string   $nodeString  The node string
     * @param      integer  $line         The line
     *
     * @return     Node
     */
    final private static function onSpecial(string $nodeString, int $line):Node
    {
        return $nodeString[0] === "#" ? new NodeComment($nodeString, $line) : new NodeDirective($nodeString, $line);
    }

    /**
     * Set $node type and value when $nodevalue starts with a quote (simple or double)
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onQuoted(string $nodeString, int $line):Node
    {
        $trimmed = trim($nodeString);
        return Regex::isProperlyQuoted($trimmed) ? new NodeQuoted($trimmed, $line) : new NodeScalar($trimmed, $line);
    }

    /**
     * Set $node type and value when NodeValue starts with a Set characters "?:"
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onSetElement(string $nodeString, int $line):Node
    {
        return $nodeString[0] === '?' ? new NodeSetKey($nodeString, $line) : new NodeSetValue($nodeString, $line);
    }

    /**
     * Determines the Node type and value when a compact object/array syntax is found
     *
     * @param string $nodeString The value assumed to start with { or [ or characters
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onCompact(string $nodeString, int $line):Node
    {
        $json = json_decode($nodeString, false, 512, self::JSON_OPTIONS);
        if (json_last_error() === \JSON_ERROR_NONE)       return new NodeJSON($nodeString, $line, $json);
        elseif (preg_match(Regex::MAPPING, $nodeString))  return new NodeCompactMapping($nodeString, $line);
        elseif (preg_match(Regex::SEQUENCE, $nodeString)) return new NodeCompactSequence($nodeString, $line);
        else {
            return new NodePartial($nodeString, $line);
        }
    }

    /**
     * Determines Node type and value when an hyphen "-" is found
     *
     * @param string $nodeString The node string value
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onHyphen(string $nodeString, int $line):Node
    {
        if (substr($nodeString, 0, 3) === '---')       return new NodeDocStart($nodeString, $line);
        elseif (preg_match(Regex::ITEM, $nodeString))  return new NodeItem($nodeString, $line);
        else {
            return new NodeScalar($nodeString, $line);
        }
    }

    /**
     * Sets Node type and value according to $nodeString when one of these characters is found : !,&,*
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     */
    final private static function onNodeAction(string $nodeString, int $line):Node
    {
        if ($nodeString[0] === '!')     return new NodeTag($nodeString, $line);
        elseif ($nodeString[0] === '&') return new NodeRefDef($nodeString, $line);
        elseif ($nodeString[0] === '*') return new NodeRefCall($nodeString, $line);
        else {
            throw new \ParseError("Not a action node !! $nodeString[0]");
        }
    }


    final private static function onLiteral(string $nodeString, int $line):Node
    {
        if ($nodeString[0] === '>')      return new NodeLitFolded($nodeString, $line);
        elseif ($nodeString[0] === '|')  return new NodeLit($nodeString, $line);
        else {
            throw new \ParseError("Not a literal node !! $nodeString[0]");
        }
    }

}