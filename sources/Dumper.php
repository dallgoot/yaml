<?php
namespace Dallgoot\Yaml;

use \SplDoublyLinkedList as DLL;

/**
 *  Convert PHP datatypes to a YAML string syntax
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Dumper
{
    private const INDENT = 2;
    // private const WIDTH  = 120; /// forget this feature for the moment
    private const OPTIONS = 00000;
    private const DATE_FORMAT = 'Y-m-d';

    /** @var null|\SplDoublyLinkedList */
    private static $result;
    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;
    /** @var int */
    public static $floatPrecision = 4;

    // public function __construct(int $options = null)
    // {
    //     if (is_int($options)) self::$options = $options;
    // }

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
        if (is_scalar($dataType)) {
            // TODO: what to woth comments ???
            return "--- ".DumperHandlers::dumpScalar($dataType, 0). self::LINEFEED ;
        }
        return DumperHandlers::dump($dataType, $options);
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

    /**
     * Dump (determine) the string value according to type
     *
     * @param string  $dataType The data type
     * @param integer $indent   The indent
     *
     * @return string The YAML representation of $dataType
     */
    private static function dump($dataType, int $indent)
    {
        if (is_scalar($dataType)) {
            if ($dataType === \INF) return '.inf';
            if ($dataType === -\INF) return '-.inf';
            switch (gettype($dataType)) {
                case 'boolean': return $dataType ? 'true' : 'false';
                case 'float': //fall through
                case 'double': return is_nan((double) $dataType) ? '.nan' : sprintf('%.'.self::$floatPrecision.'F', $dataType);
                default:
                    return $dataType;
            }
        } elseif (is_object($dataType)) {
            return self::dumpObject($dataType, $indent);
        } elseif (is_array($dataType)) {
            return self::dumpArray($dataType, $indent);
        }
    }

    /**
     * Dumps an YamlObject (YAML document) as a YAML string
     *
     * @param YamlObject $obj The object
     */
    private static function dumpYamlObject(YamlObject $obj)
    {
        if ($obj->hasDocStart() && self::$result instanceof DLL) self::$result->push("---");
        // self::dump($obj, 0);
        if (count($obj) > 0) {
            self::dumpArray($obj->getArrayCopy(), 0);
        } else {
            self::dumpObject($obj, 0);
        }
        // self::insertComments($obj->getComment());
        //TODO: $references = $obj->getAllReferences();
    }

    /**
     * Add $value to the current YAML representation (self::$result) and cut lines to self::WIDTH if needed
     *
     * @param string $value  The value
     * @param int    $indent The indent
     */
    private static function add(string $value, int $indent)
    {
        $newVal = str_repeat(" ", $indent).$value;
        foreach (str_split($newVal, self::WIDTH) as $chunks) {
            self::$result->push($chunks);
        }
    }

    /**
     * Insert comments from YamlObject at specific lines OR add to the value currently at the line
     *
     * @param array $commentsArray  The comments array
     */
    private static function insertComments(array $commentsArray)
    {
        foreach ($commentsArray as $lineNb => $comment) {
            self::$result->add($lineNb, $comment);
        }
    }
