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
        $trimmed = ltrim($nodeString);
        if ($trimmed === '')                                return new NodeBlank($nodeString, $line);
        elseif (substr($trimmed, 0, 3) === '...')           return new NodeDocEnd($nodeString, $line);
        elseif (preg_match(Regex::KEY, $trimmed, $matches)) return new NodeKey($nodeString, $line, $matches);
        else {
            $first = $trimmed[0];
            $stringGroups = ["-" ,'>|' ,'"\'',"#%" ,"{[" ,":?" ,'*&!'];
            $methodGroups = ['onHyphen','onLiteral','onQuoted','onSpecial','onCompact','onSetElement','onNodeAction'];
            $actions = ["-"   => 'onHyphen',
                        '>|'  => 'onLiteral',
                        '"\'' => 'onQuoted',
                        "#%"  => 'onSpecial',
                        "{["  => 'onCompact',
                        ":?"  => 'onSetElement',
                        '*&!' => 'onNodeAction'
                    ];
            foreach ($stringGroups as $groupIndex => $stringRef) {
                if (is_int(strpos($stringRef, $first))) {
                    $methodName = $methodGroups[$groupIndex];
                    try {
                        return self::$methodName($first, $nodeString, $line);
                    } catch (\Exception|\Error|\ParseError $e) {
                        throw new \Exception(" could not create a Node, ", 1, $e);
                    }
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
    final private static function onSpecial(string $first, string $nodeString, int $line):Node
    {
        return $first === "#" ? new NodeComment(ltrim($nodeString), $line)
                              : new NodeDirective(ltrim($nodeString), $line);
    }

    /**
     * Set $node type and value when $nodevalue starts with a quote (simple or double)
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onQuoted(string $first, string $nodeString, int $line):Node
    {
        return Regex::isProperlyQuoted(trim($nodeString)) ? new NodeQuoted($nodeString, $line)
                                                          : new NodePartial($nodeString, $line);
    }

    /**
     * Set $node type and value when NodeValue starts with a Set characters "?:"
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onSetElement(string $first, string $nodeString, int $line):Node
    {
        return $first === '?' ? new NodeSetKey($nodeString, $line)
                              : new NodeSetValue($nodeString, $line);
    }

    /**
     * Determines the Node type and value when a compact object/array syntax is found
     *
     * @param string $nodeString The value assumed to start with { or [ or characters
     * @param int    $line       The line
     *
     * @return     Node
     */
    final private static function onCompact(string $first, string $nodeString, int $line):Node
    {
        $json = json_decode($nodeString, false, 512, self::JSON_OPTIONS);
        if (json_last_error() === \JSON_ERROR_NONE)             return new NodeJSON($nodeString, $line);
        elseif (preg_match(Regex::MAPPING, trim($nodeString)))  return new NodeCompactMapping($nodeString, $line);
        elseif (preg_match(Regex::SEQUENCE, trim($nodeString))) return new NodeCompactSequence($nodeString, $line);
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
    final private static function onHyphen(string $first, string $nodeString, int $line):Node
    {
        if (substr($nodeString, 0, 3) === '---')              return new NodeDocStart($nodeString, $line);
        elseif (preg_match(Regex::ITEM, ltrim($nodeString)))  return new NodeItem($nodeString, $line);
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
    final private static function onNodeAction(string $first, string $nodeString, int $line):Node
    {
        if (!preg_match(Regex::NODE_ACTIONS, ltrim($nodeString), $matches)) {
            return new NodeScalar($nodeString, $line);
        }
        if (isset($matches['content'])) {
            $node = self::get($matches['content'], $line);
        } else {
            $node = new NodeTag($nodeString, $line);
        }
        $action = trim($matches['action']);
        switch ($action[0]) {
            case '!': $node->_tag    = $action;return $node;
            case '&': $node->_anchor = $action;return $node;
            case '*': return new NodeAnchor(trim($action), $line);
            default:
                throw new \ParseError("Not a action node !! '$action[0]' on line:$line".gettype($first));
        }
    }

    final private static function onLiteral(string $first, string $nodeString, int $line):Node
    {
        switch ($first) {
            case '>': return new NodeLitFolded($nodeString, $line);
            case '|': return new NodeLit($nodeString, $line);
            default:
                throw new \ParseError("Not a literal node !! '$first' on line:$line");
        }
    }

}