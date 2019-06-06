<?php
namespace Dallgoot\Yaml;

// use \SplDoublyLinkedList as DLL;

/**
 *  Convert PHP datatypes to a YAML string syntax
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Dumper
{
    private const LINEFEED = "\n";
    private const INDENT = 2;
    // private const WIDTH  = 120; /// forget this feature for the moment
    private const OPTIONS = 00000;
    private const DATE_FORMAT = 'Y-m-d';

    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;
    /** @var int */
    public static $floatPrecision = 4;

    /**
     * Returns (as a string) the YAML representation of the $dataType provided
     *
     * @param mixed    $dataType The data type
     * @param int|null $options  The options
     *
     * @throws \Exception datatype cannot be null
     *
     * @return string The Yaml string content
     */
    public static function toString($dataType, int $options = null):string
    {
        if (empty($dataType)) throw new \Exception(self::class.": No content to convert to Yaml");
        self::$options = is_int($options) ? $options : self::OPTIONS;
        $dumpHandler = new DumperHandlers($options);
        if (is_scalar($dataType)) {
            // TODO: what to woth comments ???
            return "--- ".$dumpHandler->dumpScalar($dataType). self::LINEFEED ;
        }
        return $dumpHandler->dump($dataType, 0);
    }

    /**
     * Calls and saves the result of Dumper::toString to the file $filePath provided
     *
     * @param string   $filePath The file path
     * @param mixed    $dataType The data type
     * @param int|null $options  The options
     *
     * @throws \Exception datatype cannot be null
     *
     * @return bool true = if the file has been correctly saved  ( return value from 'file_put_contents')
     */
    public static function toFile(string $filePath, $dataType, int $options = null):bool
    {
        return !is_bool(file_put_contents($filePath, self::toString($dataType, $options)));
    }

}
