<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\Nodes\NodeGeneric;

/**
 * Analyzes $nodeString
 * determines the appropriate NodeType
 * constructs it
 * and returns it
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
final class NodeFactory
{
    private const JSON_OPTIONS = \JSON_PARTIAL_OUTPUT_ON_ERROR|\JSON_UNESCAPED_SLASHES;

    final public static function get($nodeString = null, $line = 0):NodeGeneric
    {
        $trimmed = ltrim($nodeString);
        if ($trimmed === '')                                return new Nodes\Blank($nodeString, $line);
        elseif (substr($trimmed, 0, 3) === '...')           return new Nodes\DocEnd($nodeString, $line);
        elseif ((bool) preg_match(Regex::KEY, $trimmed, $matches)) return new Nodes\Key($nodeString, $line, $matches);
        else {
            $first = $trimmed[0];
            $stringGroups = ['-',         '>|' ,   '"\'',    "#%" ,    "{[" ,       ":?" ,       '*&!'];
            $methodGroups = ['Hyphen','Literal','Quoted','Special','Compact','SetElement','NodeAction'];
            foreach ($stringGroups as $groupIndex => $stringRef) {
                if (is_int(strpos($stringRef, $first))) {
                    $methodName = 'on'.$methodGroups[$groupIndex];
                    try {
                        return self::$methodName($first, $nodeString, $line);
                    } catch (\Throwable $e) {
                        throw new \Exception(" could not create a Node ($methodName) for '$nodeString'", 1, $e);
                    }
                }
            }
        }
        return new Nodes\Scalar($nodeString, $line);
    }

    /**
     * Return the correct Node Object between NodeComment OR NodeDirective
     *
     * @param      string   $nodeString  The node string
     * @param      integer  $line         The line
     *
     * @return     NodeGeneric
     */
    private static function onSpecial(string $first, string $nodeString, int $line):NodeGeneric
    {
        if ($first === "#") {
            return new Nodes\Comment(ltrim($nodeString), $line);
        } else {
            if (preg_match(Regex::DIRECTIVE_TAG, $nodeString)
                || preg_match(Regex::DIRECTIVE_VERSION, $nodeString)) {
                return new Nodes\Directive(ltrim($nodeString), $line);
            } else {
                throw new \ParseError("Invalid/Unknown Directive", 1);
            }
        }
    }

    /**
     * Set $node type and value when $nodevalue starts with a quote (simple or double)
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onQuoted(string $first, string $nodeString, int $line):NodeGeneric
    {
        return Regex::isProperlyQuoted(trim($nodeString)) ? new Nodes\Quoted($nodeString, $line)
                                                          : new Nodes\Partial($nodeString, $line);
    }

    /**
     * Set $node type and value when NodeValue starts with a Set characters "?:"
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onSetElement(string $first, string $nodeString, int $line):NodeGeneric
    {
        return $first === '?' ? new Nodes\SetKey($nodeString, $line)
                              : new Nodes\SetValue($nodeString, $line);
    }

    /**
     * Determines the Node type and value when a compact object/array syntax is found
     *
     * @param string $nodeString The value assumed to start with { or [ or characters
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onCompact(string $first, string $nodeString, int $line):NodeGeneric
    {
        json_decode($nodeString, false, 512, self::JSON_OPTIONS);
        if (json_last_error() === \JSON_ERROR_NONE) {
            return new Nodes\JSON($nodeString, $line);
        } else {
            $backtrack_setting = "pcre.backtrack_limit";
            ini_set($backtrack_setting, "-1");
            $isMapping  = preg_match(Regex::MAPPING, trim($nodeString));
            $isSequence = preg_match(Regex::SEQUENCE, trim($nodeString));
            ini_restore($backtrack_setting);

            if (is_bool($isMapping) || is_bool($isSequence)) {
                throw new \Exception("Regex Error for ".(is_bool($isMapping) ? 'mapping:' :'sequence:').preg_last_error());
            }
            if ($isMapping) {
                // var_dump(Regex::MAPPING, trim($nodeString));
                return new Nodes\CompactMapping($nodeString, $line);
            } elseif ($isSequence) {
                return new Nodes\CompactSequence($nodeString, $line);
            }
        }
        return new Nodes\Partial($nodeString, $line);
    }

    /**
     * Determines Node type and value when an hyphen "-" is found
     *
     * @param string $nodeString The node string value
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onHyphen(string $first, string $nodeString, int $line):NodeGeneric
    {
        if (substr($nodeString, 0, 3) === '---')              return new Nodes\DocStart($nodeString, $line);
        elseif ((bool) preg_match(Regex::ITEM, ltrim($nodeString)))  return new Nodes\Item($nodeString, $line);
        else {
            return new Nodes\Scalar($nodeString, $line);
        }
    }

    /**
     * Sets Node type and value according to $nodeString when one of these characters is found : !,&,*
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     *@todo replace $action[0] with $first if applicable
     */
    private static function onNodeAction(string $first, string $nodeString, int $line):NodeGeneric
    {
        if (!((bool) preg_match(Regex::NODE_ACTIONS, trim($nodeString), $matches))) {
            // var_dump("ACTION is scalar: '$nodeString'");
            return new Nodes\Scalar($nodeString, $line);
        }
        if ($first === "!") {
            return new Nodes\Tag($nodeString, $line);
        } else {
            return new Nodes\Anchor($nodeString, $line);
        }
    }

    private static function onLiteral(string $first, string $nodeString, int $line):NodeGeneric
    {
        switch ($first) {
            case '>': return new Nodes\LiteralFolded($nodeString, $line);
            case '|': return new Nodes\Literal($nodeString, $line);
            default:
                throw new \ParseError("Not a literal node !! '$first' on line:$line");
        }
    }

}