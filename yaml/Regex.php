<?php

namespace Dallgoot\Yaml;

/**
 * Define Regex patterns as constants
 * @author stephane.rebai@gmail.com
 * @license Apache 2.0
 * @link TODO : url to specific online doc
 */
class Regex
{

    // const NULL  = "null";
    // const FALSE = "false";
    // const TRUE  = "true";
    const AN = "[\w ]*";
    // const NUM = "-?[\d.e]+";
    const SIMPLE = "(?P<sv>null|false|true|[\w ]+|-?[\d.e]+)";
    private const seqForMap = "(?P<seq>\[(?:(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+\])";
    private const mapForSeq = "(?P<map>{\s*(?:".self::AN."\s*:\s*(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+})";

    const MAPPING  = "/(?P<map>{\s*(?:(['\"])".self::AN."\\2\s*:\s*(?:".self::SIMPLE."|".self::seqForMap."|(?P>map)),?\s*)+})/i";
    const SEQUENCE = "/(?P<seq>\[(?:(?:".self::SIMPLE."|".self::mapForSeq."|(?P>seq)),?\s*)+\])/i";

    const KEY  = '/^([[:alnum:]_][[:alnum:]_ -.\/]*[ \t]*)(?::[ \t](.*)|:)$/';
    const ITEM = '/^-([ \t]+(.*))?$/';


    public static function isDate($v):bool
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
                  throw new \Exception("Regex date error");
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
        return (bool) preg_match("/(['".'"]).*?(?<![\\\\])\1$/ms', $var);
    }
}
