<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Dumper;
use Dallgoot\Yaml\Loader;

/**
 * Library that :
 * - reads YAML as PHP types
 * - writes PHP types as YAML content
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 *
 * @see YamlObject
 * @see Compact
 * @see Tag
 */
final class Yaml
{
    const VERSION_SUPPORT = "1.2";
    /**
     * Parse the given Yaml string to either :
     * - a YamlObject
     * - an array of YamlObject
     *
     * @param string   $someYaml Some yaml
     * @param ?int     $options  from Loader class, bitwise combination of
     *                           Loader::IGNORE_DIRECTIVES
     *                           Loader::IGNORE_COMMENTS
     *                           Loader::NO_PARSING_EXCEPTIONS
     *                           Loader::NO_OBJECT_FOR_DATE
     * @param ?int      $debug    define the level of debugging (true = default)
     *
     * @return YamlObject|array|null a Yaml document as YamlObject OR multiple documents as an array of YamlObject,
     *                               NULL if Error and option Loader::NO_PARSING_EXCEPTIONS is set.
     * @throws \Exception coming from Dallgoot\Yaml\Loader
     * @see    Dallgoot\Yaml\Loader
     *
     * @todo transpose Loader::NO_PARSING_EXCEPTIONS in this class
     */
    public static function parse(string $someYaml, ?int $options = null, ?int $debug = null)
    {
        try {
            return (new Loader(null, $options, $debug))->parse($someYaml);
        } catch (\Throwable $e) {
            throw new \Exception(__CLASS__ . " Error while parsing YAML string", 1, $e);
        }
    }

    /**
     * Load the given file and parse its content (assumed YAML) to either :
     * - a YamlObject
     * - an array of YamlObject
     *
     * @param string   $fileName Some file path name
     * @param ?int     $options  from Loader class, bitwise combination of
     *                           Loader::IGNORE_DIRECTIVES
     *                           Loader::IGNORE_COMMENTS
     *                           Loader::NO_PARSING_EXCEPTIONS
     *                           Loader::NO_OBJECT_FOR_DATE
     * @param ?int     $debug    define the level of debugging (true = default)
     *
     * @return YamlObject|array|null a Yaml document as YamlObject OR multiple documents as an array of YamlObject,
     *                               NULL if Error
     * @throws \Exception coming from Dallgoot\Yaml\Loader
     * @see    Dallgoot\Yaml\Loader
     */
    public static function parseFile(string $fileName, ?int $options = null, ?int $debug = null)
    {
        try {
            return (new Loader($fileName, $options, (int) $debug))->parse();
        } catch (\Throwable $e) {
            throw new \Exception(__CLASS__ . " Error during parsing '$fileName'", 1, $e);
        }
    }

    /**
     * Returns the YAML representation corresponding to given PHP variable
     *
     * @param mixed    $somePhpVar Some php variable
     * @param ?int     $options    enable/disable some options see Dumper
     *
     * @return string  ( the representation of $somePhpVar as a YAML content (single or multiple document accordingly) )
     * @throws \Exception on errors during building YAML string coming from Dumper class
     * @see    Dumper
     */
    public static function dump($somePhpVar, ?int $options = null): string
    {
        try {
            return (new Dumper($options))->toString($somePhpVar);
        } catch (\Throwable $e) {
            throw new \Exception(__CLASS__ . " Error dumping", 1, $e);
        }
    }


    /**
     * Builds the YAML representation corresponding to given PHP variable ($somePhpVar)
     * AND save it as file with the $fileName provided.
     *
     * @param string   $fileName   The file name
     * @param mixed    $somePhpVar Some php variable
     * @param ?int     $options    Dumper::constants as options
     *
     * @return boolean  true if YAML built and saved , false if error during writing file
     * @throws \Exception on errors (from Dumper::toString) during building YAML string
     * @see    Dumper
     */
    public static function dumpFile(string $fileName, $somePhpVar, ?int $options = null): bool
    {
        try {
            return (new Dumper($options))->toFile($fileName, $somePhpVar);
        } catch (\Throwable $e) {
            throw new \Exception(__CLASS__ . " Error during dumping '$fileName'", 1, $e);
        }
    }
}
