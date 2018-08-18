<?php

namespace Dallgoot\Yaml;

/**
 * TODO
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 */
final class Yaml
{
    const BLANK            = 1;
    const COMMENT          = 2;
    const COMPACT_MAPPING  = 4;
    const COMPACT_SEQUENCE = 8;
    const DIRECTIVE        = 16;
    const DOC_END          = 32;
    const DOC_START        = 64;
    const ITEM             = 128;
    const JSON             = 256;
    const KEY              = 512;
    const LITT             = 1024; //litteral
    const LITT_FOLDED      = 2048; //litteral
    const MAPPING          = 4096;
    const PARTIAL          = 8192;
    const QUOTED           = 16384;
    const RAW              = 32768;
    const REF_CALL         = 65536; //reference
    const REF_DEF          = 131072; //reference
    const ROOT             = 262144;
    const SCALAR           = 524288;
    const SEQUENCE         = 1048576;
    const SET              = 2097152;
    const SET_KEY          = 4194304;
    const SET_VALUE        = 8388608;
    const TAG              = 16777216;

    const LITTERALS = self::LITT|self::LITT_FOLDED;

    /* @var null|array */
    public static $TYPE_NAMES = null;

    /**
     * Gets the name for a given constant declared in the Dallgoot\Yaml namespace
     * @param      integer  $typeInteger       The constant value
     *
     * @return     string    The name.
     */
    public static function getName(int $typeInteger):string
    {
        if (is_null(self::$TYPE_NAMES)) {
            $oClass = new \ReflectionClass(__CLASS__);
            self::$TYPE_NAMES = array_flip($oClass->getConstants());
            // $f = function ($v) { return str_replace('Dallgoot\Yaml\\', '', $v);};
            // self::$TYPE_NAMES = array_map($f, array_flip(get_defined_constants(true)['user']));
        }
        return self::$TYPE_NAMES[$typeInteger];
    }

    /**
     * Parse the given Yaml string to a PHP type
     *
     * @param      string  $someYaml        Some yaml
     *
     * @return     YamlObject|array    ( return a PHP type representation with Yaml document as YamlObject and multiple
     * documents as an array of YamlObject )
     */
    public static function parse(string $someYaml, $options = null, $debug = null)
    {
        return (new Loader(null, $options, $debug))->parse($someYaml);
    }

    /**
     * Load the given file and parse its content (assumed YAML) to a PHP type
     *
     * @param string  $someYaml      Some yaml
     *
     * @return YamlObject|array    ( return a PHP type representation with Yaml document as YamlObject and multiple
     * documents as an array of YamlObject )
     */
    public static function parseFile(string $fileName, $options = null, $debug = null)
    {
        return (new Loader($fileName, $options, $debug))->parse();
    }

    /**
     * Returns the YAML representation corresponding to given PHP variable
     *
     * @param mixed  $somePhpVar       Some php variable
     * @param int|null $options       Dumper::constants as options
     *
     * @return     string  ( the representation of $somePhpVar as a YAML content (single or multiple document according to argument) )
     * @throws   Exception on errors during building YAML string
     * @see Dumper::toString
     */
    public static function dump($somePhpVar, $options = null):string
    {
        return Dumper::toString($somePhpVar, $options);
    }

    /**
     * Builds the YAML representation corresponding to given PHP variable ($somePhpVar)
     * AND save it as file with the $fileName provided.
     *
     * @param string   $fileName       The file name
     * @param mixed   $somePhpVar     Some php variable
     * @param int|null $options       Dumper::constants as options
     *
     * @return     boolean  true if YAML built and saved , false otherwise
     * @throws   Exception on errors during building YAML string
     */
    public static function dumpFile(string $fileName, $somePhpVar, $options = null):bool
    {
        return Dumper::toFile($fileName, $somePhpVar, $options);
    }
}
