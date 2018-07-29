<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;
// declaring constants for Dallgoot\Yaml
$TYPES = ['DIRECTIVE',
            'DOC_START',
            'DOC_END',
            'COMMENT',
            'BLANK',
            'ROOT',
            'KEY',
            'ITEM',
            'MAPPING',
            'SEQUENCE',
            'MAPPING_SHORT',
            'SEQUENCE_SHORT',
            'PARTIAL',
            'LITT', //litteral
            'LITT_FOLDED',//litteral
            'SCALAR',
            'TAG',
            'JSON',
            'QUOTED',
            'RAW',
            'REF_DEF', //reference
            'REF_CALL', //reference
            'SET',
            'SET_KEY',
            'SET_VALUE'];


foreach ($TYPES as $power => $name) {
    define(__NAMESPACE__."\\$name", 2**$power);
}

const LITTERALS = Y\LITT | Y\LITT_FOLDED;
// print_r(get_defined_constants(true)['user']);

namespace Dallgoot;

class Yaml
{
    /* @var null|array */
    private static $TYPE_NAMES = null;

    /**
     * Gets the name for a given constant declared in the Dallgoot\Yaml namespace
     * @param      integer  $typeInteger  The constant value
     *
     * @return     string    The name.
     */
    public static function getName($typeInteger)
    {
        if(is_null(self::$TYPE_NAMES)) {
            $f = function ($v) { return str_replace('Dallgoot\Yaml\\', '', $v);};
            self::$TYPE_NAMES = array_map($f, array_flip(get_defined_constants(true)['user']));
        }
        return self::$TYPE_NAMES[$typeInteger];
    }

    /**
     * Parse the given Yaml string to a PHP type
     *
     * @param      string  $someYaml  Some yaml
     *
     * @return     YamlObject|array    ( return a PHP type representation with Yaml document as YamlObject and multiple
     * documents as an array of YamlObject )
     */
    public static function parse(string $someYaml)
    {
        return (new Yaml\Loader)->parse($someYaml);
    }

    /**
     * Load the given file and parse its content (assumed YAML) to a PHP type
     *
     * @param      string  $someYaml  Some yaml
     *
     * @return     YamlObject|array    ( return a PHP type representation with Yaml document as YamlObject and multiple
     * documents as an array of YamlObject )
     */
    public static function parseFile(string $fileName)
    {
        return (new Yaml\Loader($fileName))->parse();
    }

    /**
     * Returns the YAML representation corresponding to given PHP variable
     *
     * @param      mixed  $somePhpVar  Some php variable
     *
     * @return     string  ( the representation of $somePhpVar as a YAML content (single or multiple document according to argument) )
     * @throws   Exception on errors during building YAML string
     * @see Dumper::toString
     */
    public static function dump($somePhpVar):string
    {
        return Yaml\Dumper::toString($somePhpVar);
    }

    /**
     * Builds the YAML representation corresponding to given PHP variable ($somePhpVar)
     * AND save it as file with the $fileName provided.
     *
     * @param      string   $fileName    The file name
     * @param      mixed   $somePhpVar  Some php variable
     *
     * @return     boolean  true if YAML built and saved , false otherwise
     * @throws   Exception on errors during building YAML string
     */
    public static function dumpFile(string $fileName, $somePhpVar):boolean
    {
        return Yaml\Dumper::toFile($fileName, $somePhpVar);
    }
}
