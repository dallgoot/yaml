<?php
namespace Dallgoot\Yaml;

class Types
{
    const DIRECTIVE  = 0;
    const DOC_START = 1;
    const DOC_END = 2;

    const COMMENT = 8;
    const EMPTY   = 16;
    const ROOT    = 32;

    const KEY = 42;
    const ITEM = 52;

    const MAPPING  = 43;
    const SEQUENCE = 53;

    const MAPPING_SHORT  = 44;
    const SEQUENCE_SHORT = 54;

    const PARTIAL = 62; // have a multi line quoted  string OR json definition
    const LITTERAL = 72;
    const LITTERAL_FOLDED = 82;

    const STRING    = 102;
    const BOOLEAN = 112;
    const NUMBER  = 122;
    const TAG = 132;
    const JSON = 142;

    const QUOTED = 148;
    const REF_DEF = 152;
    const REF_CALL = 164;
    public static $NOTBUILDABLE = [self::DIRECTIVE,
                                    self::ROOT,
                                    self::DOC_END,
                                    self::COMMENT,
                                    self::EMPTY,
                                    self::TAG];
    public static $LITTERALS = [self::LITTERAL, self::LITTERAL_FOLDED];

    public function __construct()
    {
        // self::test = 3;
    }

    public static function getName($constant)
    {
        return array_flip((new \ReflectionClass(self::class))->getConstants())[$constant];
    }
}
