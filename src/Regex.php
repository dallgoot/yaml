<?php

namespace Dallgoot\Yaml;

/**
 * Define Regex patterns as constants
 * and some 'testing-type' methods
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Regex
{
    const OCTAL_NUM = "/^0o\d+$/i";
    const HEX_NUM   = "/^0x[\da-f]+$/i";
    const BIN_NUM   = "/^0b[01]+$/i";

    const QUOTED = "(?'quot'(?'q'['\"]).*?(?<![\\\\])(?&q))";
    const NUM    = "(?'num'[\\-+]?(?:\\d+\\.?(?:\\d*(e[+\\-]?\\d+)?)|(\\.(inf|nan))))";
    const WORD   = "(?'word'[[:alnum:] _\\-\\.:]+)";
    const RC     = "(?'rc'\\*\\w+)"; //reference call
    // const RC     = "(?'rc'\\*[^ *&]+)"; //reference call
    // const RD     = "(?'rd'&\\w+)"; //reference definition
    const RD     = "(?'rd'&[^ ]+)"; //reference definition
    // const TAG    = "(?'tag'!!?[\\w\\/\\-]+!?)";
    const TAG    = "(?'tag'!!?[^! ]+!?)";
    const ALL    = "(?'all'(?:(?:(?&rd)|(?&tag)) +)?(?:(?&quot)|(?&rc)|(?&word)|(?&map)|(?&seq)))";
    const MAP    = "(?'map'\\{ *?(?'pair'((?:(?&quot)|[^:]+) *?: *(?&all)) *,? *)* *?\\})";
    // const MAP    = "(?'map'\\{ *((?:(?&quot)|(?&word)) *: *(?&all) *(?:, *(?:(?&quot)|(?&word)) *: *(?&all)))*\\})";
    // const SEQ    = "(?'seq'\\[ *(?:(?'i'(?&all)) *,? *)* *\\])";
    const SEQ    = "(?'seq'\\[ *((?&all) *(?:, *(?&all))*)* *\\])";
    const ALLDEF = "(?(DEFINE)".Regex::QUOTED.
                                Regex::RC.
                                Regex::WORD.
                                Regex::TAG.
                                Regex::RD.
                                Regex::ALL.
                                Regex::MAP.
                                Regex::SEQ.")";

    const MAPPING  = "/".Regex::ALLDEF."^(?&map)$/i";
    const MAPPING_VALUES = "/".Regex::ALLDEF."(?'k'(?&quot)|[^:]+) *: *(?'v'(?&all)) *,? */i";

    const SEQUENCE = "/".Regex::ALLDEF."^(?&seq)$/i";
    const SEQUENCE_VALUES = "/".Regex::ALLDEF."(?'item'(?&all)) *,? */i";

    const KEY  = "/^([\\w'\"~!][\\w'\" \\-.\\/~!]*[ \\t]*)(?::([ \\t]+[^\\n]+)|:[ \\t]*)$/i";
    # const KEY  = '/^([^:#]+)[ \t]*:([ \t]+.+)*$/iu';
    const ITEM = '/^-([ \t]+(.*))?$/';

    const NODE_ACTIONS = "/(?(DEFINE)".Regex::RC.Regex::RD.Regex::TAG.")(?'action'(?&rc)|(?&rd)|(?&tag))( +(?'content'.*))?$/";

    // %TAG ! tag:example.com,2000:app/
    // %TAG !! tag:example.com,2000:app/
    // %TAG !e! tag:example.com,2000:app/
    // %TAG !m! !my-
    // !<!bar> baz
    // !<tag:clarkevans.com,2002:invoice>
    const TAG_URI = "(?'url'tag:\\w+\\.\\w{2,},\\d{4}:\\w*)";
    const TAG_PARTS = "/(?'handle'!(?:[\\w\\d\\-_]!|!)*)(?'tagname'(?:<!?)?[\\w\\d\\-:.,_]+>?)?/i";
    const DIRECTIVE_TAG = "/(?(DEFINE)".Regex::TAG_URI.")%TAG +(?'handle'![\\w\\d\-_]+!|!!|!) +(?'uri'(?&url)|(?'prefix'![\\w\\d\-_]+))/i";
    const DIRECTIVE_VERSION = "/%YAML *:? *(?'version'1\\.\\d)/i";


    /**
     * Determines if a valid Date format
     * @param string $v a string value
     * @return bool
     * @throws \Exception if any preg_match has invalid regex
     * @todo : support other date formats ???
     */
    public static function isDate(string $v):bool
    {
        $d         = "\\d{4}([-\\/])\\d{2}\\1\\d{2}";
        $h         = "\\d{2}(:)\\d{2}\\2\\d{2}";
        $date      = "/^$d$/"; // 2002-12-14, 2002/12/14
        $canonical = "/^$d(?:t| )$h\\.\\dz?$/im"; // 2001-12-15T02:59:43.1Z
        $spaced    = "/^$d(?:t| )$h\\.\\d{2} [-+]\\d$/im"; // 2001-12-14 21:59:43.10 -5
        $iso8601   = "/^$d(?:t| )$h\\.\\d{2}[-+]\\d{2}\\2\\d{2}/im"; // 2001-12-14t21:59:43.10-05:00
        $matchDate      = preg_match($date, $v);
        $matchCanonical = preg_match($canonical, $v);
        $matchSpaced    = preg_match($spaced, $v);
        $matchIso       = preg_match($iso8601, $v);

        return $matchDate || $matchCanonical || $matchSpaced || $matchIso;
    }

    /**
     * Determines if number.
     *
     * @param string $var A string value
     *
     * @return boolean  True if number, False otherwise.
     */
    public static function isNumber(string $var):bool
    {
        // && (bool) preg_match("/^((0o\d+)|(0x[\da-f]+)|([\d.]+e[-+]\d{1,2})|([-+]?(\d*\.?\d+)))$/i", $var);
        return is_numeric($var)
                || (bool) preg_match(Regex::OCTAL_NUM, $var)
                || (bool) preg_match(Regex::HEX_NUM, $var)
                || (bool) preg_match(Regex::BIN_NUM, $var);
    }

    /**
     * Determines if properly quoted.
     *
     * @param string $var The variable
     *
     * @return boolean True if properly quoted, False otherwise.
     */
    public static function isProperlyQuoted(string $var):bool
    {
        return (bool) preg_match("/^(['\"]).*?(?<![\\\\])\\1$/s", trim($var));
    }
}
