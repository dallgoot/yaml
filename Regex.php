<?php

namespace Dallgoot\Yaml;

/**
 * Define Regex patterns as constants
 */
class Regex
{

    const NULL  = "null";
    const FALSE = "false";
    const TRUE  = "true";
    const AN = "[\w ]+";
    const NUM = "-?[\d.e]+";
    const SIMPLE = "(?P<sv>".self::NULL."|".
                                  self::FALSE."|".
                                  self::TRUE."|".
                                  self::AN."|".
                                  self::NUM.")";
    private const seqForMap = "(?P<seq>\[(?:(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+\])";
    private const mapForSeq = "(?P<map>{\s*(?:".self::AN."\s*:\s*(?:(?P>sv)|(?P>seq)|(?P>map)),?\s*)+})";

    const MAPPING  = "/(?P<map>{\s*(?:".self::AN."\s*:\s*(?:".self::SIMPLE."|".self::seqForMap."|(?P>map)),?\s*)+})/i";
    const SEQUENCE = "/(?P<seq>\[(?:(?:".self::SIMPLE."|".self::mapForSeq."|(?P>seq)),?\s*)+\])/i";
}
