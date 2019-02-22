<?php

namespace Dallgoot\Yaml;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Yaml
{
    /**
     * Parse the given Yaml string to a PHP type
     *
     * @param string   $someYaml Some yaml
     * @param int|null $options  from Loader class, bitwise combination of
     *                           Loader::IGNORE_DIRECTIVES
     *                           Loader::IGNORE_COMMENTS
     *                           Loader::NO_PARSING_EXCEPTIONS
     *                           Loader::NO_OBJECT_FOR_DATE
     * @param int|null $debug    define the level of debugging (true = default)
     *
     * @return YamlObject|array|null a Yaml document as YamlObject OR multiple documents as an array of YamlObject,
     *                               NULL if Error
     * @throws \Exception coming from Dallgoot\Yaml\Loader
     * @see    Dallgoot\Yaml\Loader
     */
    public static function parse(string $someYaml, $options = null, $debug = null)
    {
        try {
            return (new Loader(null, $options, $debug))->parse($someYaml);
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception(__CLASS__." Error while parsing YAML string", 1, $e);
        }
    }

    /**
     * Load the given file and parse its content (assumed YAML) to a PHP type
     *
     * @param string   $fileName Some file path name
     * @param int|null $options  from Loader class, bitwise combination of
     *                           Loader::IGNORE_DIRECTIVES
     *                           Loader::IGNORE_COMMENTS
     *                           Loader::NO_PARSING_EXCEPTIONS
     *                           Loader::NO_OBJECT_FOR_DATE
     * @param int|null $debug    define the level of debugging (true = default)
     *
     * @return YamlObject|array|null a Yaml document as YamlObject OR multiple documents as an array of YamlObject,
     *                               NULL if Error
     * @throws \Exception coming from Dallgoot\Yaml\Loader
     * @see    Dallgoot\Yaml\Loader
     */
    public static function parseFile(string $fileName, $options = null, $debug = null)
    {
        try {
            return (new Loader($fileName, $options, $debug))->parse();
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception(__CLASS__." Error during parsing '$fileName'", 1, $e);
        }

    }

    /**
     * Returns the YAML representation corresponding to given PHP variable
     *
     * @param mixed    $somePhpVar Some php variable
     * @param int|null $options    enable/disable some options see Dumper
     *
     * @return string  ( the representation of $somePhpVar as a YAML content (single or multiple document accordingly) )
     * @throws \Exception on errors during building YAML string coming from Dumper class
     * @see    Dumper
     */
    public static function dump($somePhpVar, $options = null):string
    {
        try {
            return Dumper::toString($somePhpVar, $options);
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception(__CLASS__." Error dumping", 1, $e);
        }
    }


    /**
     * Builds the YAML representation corresponding to given PHP variable ($somePhpVar)
     * AND save it as file with the $fileName provided.
     *
     * @param string   $fileName   The file name
     * @param mixed    $somePhpVar Some php variable
     * @param int|null $options    Dumper::constants as options
     *
     * @return boolean  true if YAML built and saved , false if error during writing file
     * @throws \Exception on errors (from Dumper::toString) during building YAML string
     * @see    Dumper
     */
    public static function dumpFile(string $fileName, $somePhpVar, $options = null):bool
    {
        try {
            return Dumper::toFile($fileName, $somePhpVar, $options);
        } catch (\Exception|\Error|\ParseError $e) {
            throw new \Exception(__CLASS__." Error during dumping '$fileName'", 1, $e);
        }
    }
}

function isOneOf(object $obj, array $comparison)
{
    if(!is_object($obj) || !is_array($comparison)) {
        throw new Exception(__FUNCTION__." only object and array are allowed for comparison", 1);
    }
    foreach ($comparison as $className) {
        $fqn = __NAMESPACE__."\\$className";
        if ($obj instanceof $fqn) return true;
    }
    return false;
}