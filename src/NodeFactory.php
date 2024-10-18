<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes as Nodes;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;

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
class NodeFactory
{
    private const JSON_OPTIONS = \JSON_PARTIAL_OUTPUT_ON_ERROR | \JSON_UNESCAPED_SLASHES;

    final public static function get(string $nodeString, int $line = 0, bool $debug = false): NodeGeneric
    {
        $trimmed = ltrim($nodeString);
        $match = (bool) preg_match(Regex::KEY, $trimmed, $matches);
        $node = match(true) {
            $trimmed === '' => new Blank($nodeString, $line),
            str_starts_with($trimmed, '...') => new Nodes\DocEnd($nodeString, $line),
            $match => new Nodes\Key($nodeString, $line, $matches),
            default => self::onCharacter($trimmed[0], $nodeString, $line)
        };
        if ($debug) echo $line . ":" . get_class($node) . "\n";
        return $node;
    }

    /**
     * This is a slimmer version og NodeFactory::get
     * in the case of a Key value we can ignore certain cases and remove ambiguity and errors
     * 
     * @param string $nodeString
     * @param int $line
     * @param bool $debug
     * @return \Dallgoot\Yaml\Nodes\Generic\NodeGeneric
     */
    public static function getKeyValue(string $nodeString, int $line = 0, bool $debug = false): NodeGeneric
    {
        $trimmed = ltrim($nodeString);
        $node = match(true) {
            $trimmed === '' => new Blank($nodeString, $line),
            default => self::onCharacter($trimmed[0], $nodeString, $line)
        };
        if ($debug) echo $line . ":" . get_class($node) . "\n";
        return $node;
    }

    private static function onCharacter(string $first, string $nodeString, int $line): NodeGeneric
    {
        return match ($first) {
            '-' => self::onHyphen($nodeString, $line),
            '>' => new Nodes\LiteralFolded($nodeString, $line),
            '|' => new Nodes\Literal($nodeString, $line),
            '"', "'" => self::onQuoted($first, $nodeString, $line),
            '#' => new Nodes\Comment(ltrim($nodeString), $line),
            '%' => self::onDirective($nodeString, $line),
            '{', '[' => self::onCompact($nodeString, $line),
            ':' => new Nodes\SetValue($nodeString, $line),
            '?' => new Nodes\SetKey($nodeString, $line),
            '*', '&' => self::onNodeAction($nodeString, $line),
            '!' => new Nodes\Tag($nodeString, $line),
            default => new Nodes\Scalar($nodeString, $line),
        };
    }


    /**
     * Return the correct Node Object between NodeComment OR NodeDirective
     *
     * @param      string   $nodeString  The node string
     * @param      integer  $line         The line
     *
     * @return     NodeGeneric
     */
    private static function onDirective(string $nodeString, int $line): NodeGeneric
    {
            if (
                (bool) preg_match(Regex::DIRECTIVE_TAG, $nodeString)
                || (bool) preg_match(Regex::DIRECTIVE_VERSION, $nodeString)
            ) {
                return new Nodes\Directive(ltrim($nodeString), $line);
            } else {
                throw new \ParseError("Invalid/Unknown Directive", 1);
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
    private static function onQuoted(string $first, string $nodeString, int $line): NodeGeneric
    {
        return Regex::isProperlyQuoted(trim($nodeString)) ? new Nodes\Quoted($nodeString, $line)
            : new Nodes\Partial($nodeString, $line);
    }

    /**
     * Determines the Node type and value when a compact object/array syntax is found
     *
     * @param string $nodeString The value assumed to start with { or [ or characters
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onCompact(string $nodeString, int $line): NodeGeneric
    {
        json_decode($nodeString, false, 512, self::JSON_OPTIONS);
        if (json_last_error() === \JSON_ERROR_NONE) {
            return new Nodes\JSON($nodeString, $line);
        } else {
            $backtrack_setting = "pcre.backtrack_limit";
            ini_set($backtrack_setting, "-1");
            $isMapping  = (bool) preg_match(Regex::MAPPING, trim($nodeString));
            $isSequence = (bool) preg_match(Regex::SEQUENCE, trim($nodeString));
            ini_restore($backtrack_setting);

            return match(true) {
                $isMapping => new Nodes\CompactMapping($nodeString, $line),
                $isSequence => new Nodes\CompactSequence($nodeString, $line),
                default => new Nodes\Partial($nodeString, $line),
            };
        }
    }

    /**
     * Determines Node type and value when an hyphen "-" is found
     *
     * @param string $nodeString The node string value
     * @param int    $line       The line
     *
     * @return     NodeGeneric
     */
    private static function onHyphen(string $nodeString, int $line): NodeGeneric
    {
        return match(true) {
            str_starts_with($nodeString, '---') => new Nodes\DocStart($nodeString, $line),
            (bool) preg_match(Regex::ITEM, ltrim($nodeString)) => new Nodes\Item($nodeString, $line),
            default => new Nodes\Scalar($nodeString, $line),
        };
    }

    /**
     * Sets Node type and value according to $nodeString when one of these characters is found : !,&,*
     *
     * @param string $nodeString The node value
     * @param int    $line       The line
     *
     *@todo replace $action[0] with $first if applicable
     */
    private static function onNodeAction(string $nodeString, int $line): NodeGeneric
    {
        if (!((bool) preg_match(Regex::NODE_ACTIONS, trim($nodeString), $matches))) {
            return new Nodes\Scalar($nodeString, $line);
        }
        return new Nodes\Anchor($nodeString, $line);
    }

}
