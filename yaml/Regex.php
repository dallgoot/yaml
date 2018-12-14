<?php

namespace Dallgoot\Yaml;

/**
 * Define Regex patterns as constants
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Regex
{
    const QUOTED = "(?'quot'(?'q'['\"]).*?(?<![\\\\])(?&q))";
    const NUM    = "(?'num'[-+]?(?:\\d+\\.?(?:\\d*(e[+-]?\\d+)?)|(\\.(inf|nan))))";
    const WORD   = "(?'word'[\\w ]+)";
    const RC     = "(?'rc'\\*\\w+)";
    const RD     = "(?'rd'&\\w+)";
    const TAG    = "(?'tag'!+\\w+)";
    const ALL    = "(?'all'(?:(?:(?&rd)|(?&tag)) +)?(?:(?&quot)|(?&num)|(?&rc)|(?&word)|(?&map)|(?&seq)))";
    const MAP    = "(?'map'\\{ *?(?'pair'((?:(?&quot)|\\w+) *?: *(?&all)) *,? *)* *?\\})";
    const SEQ    = "(?'seq'\\[ *(?:(?'i'(?&all)) *,? *)* *\\])";
    const ALLDEF = "(?(DEFINE)".self::QUOTED.self::NUM.self::RC.self::WORD.self::TAG.self::RD.self::ALL.self::MAP.self::SEQ.")";

    const MAPPING  = "/".self::ALLDEF."(?&map)$/";
    const MAPPING_VALUES = "/".self::ALLDEF."(?'k'(?&quot)|\\w+) *: *(?'v'(?&all))?/i";

    const SEQUENCE = "/".self::ALLDEF."(?&seq)/";
    const SEQUENCE_VALUES = "/".self::ALLDEF."(?'item'(?&all)) *,? */i";


    const KEY  = '/^([[:alnum:]_\'"~][[:alnum:]_ -.\/~]*[ \t]*)(?::[ \t]([^\n]+)|:)$/i';
    const ITEM = '/^-([ \t]+(.*))?$/';



    /**
     * @param string $v a string value
     * @return bool
     * @throws \Exception if any preg_match has invalid regex
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
        if (is_bool($matchDate) || is_bool($matchCanonical) || is_bool($matchSpaced) || is_bool($matchIso)) {
            throw new \Exception(self::class." regex error for dates");
        }
        return $matchDate || $matchCanonical || $matchSpaced || $matchIso;
    }

    public static function isNumber(string $var):bool
    {
        //TODO: https://secure.php.net/manual/en/function.is-numeric.php
        return (bool) preg_match("/^((0o\d+)|(0x[\da-f]+)|([\d.]+e[-+]\d{1,2})|([-+]?(\d*\.?\d+)))$/i", $var);
    }

    public static function isProperlyQuoted(String $var):bool
    {
        return (bool) preg_match("/^(['\"]).*?(?<![\\\\])\\1$/s", $var);
    }
}
