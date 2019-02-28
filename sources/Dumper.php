<?php
namespace Dallgoot\Yaml;

use \SplDoublyLinkedList as DLL;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Dumper
{
    private const INDENT = 2;
    private const WIDTH  = 120;
    private const OPTIONS = 00000;
    private const DATE_FORMAT = 'Y-m-d';

    /** @var null|\SplDoublyLinkedList */
    private static $result;
    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;
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
        if (is_null($dataType)) throw new \Exception(self::class.": No content to convert to Yaml");
        self::$options = is_int($options) ? $options : self::OPTIONS;
        self::$result = new DLL;
        if ($dataType instanceof YamlObject) {
            self::dumpYamlObject($dataType);
        } elseif (is_array($dataType) && $dataType[0] instanceof YamlObject) {
            array_map([self::class, 'dumpYamlObject'], $dataType);
        } else {
            self::dump($dataType, 0);
        }
        $out = implode("\n", iterator_to_array(self::$result));
        self::$result = null;
        return $out;
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
     * Dumps an array as a YAML sequence
     *
     * @param array   $array  The array
     * @param integer $indent The indent
     */
    private static function dumpArray(array $array, int $indent)
    {
        $refKeys = range(0, count($array));
        foreach ($array as $key => $item) {
            $lineStart = current($refKeys) === $key ? "- " : "- $key: ";
            if (is_scalar($item)) {
                self::add($lineStart.self::dump($item,0), $indent);
            } else {
                self::add(rtrim($lineStart), $indent);
                self::dump($item, $indent + self::INDENT);
            }
            next($refKeys);
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

    /**
     * Dumps an object as a YAML mapping.
     *
     * @param      object   $obj     The object
     * @param      integer  $indent  The indent
     *
     */
    private static function dumpObject(object $obj, int $indent)
    {
        if ($obj instanceof Tag) {
            if (is_scalar($obj->value)) {
                self::add("!".$obj->tagName.' '.$obj->value, $indent);
            } else {
                self::add("!".$obj->tagName, $indent);
                self::add(self::dump($obj->value, $indent + self::INDENT), $indent + self::INDENT);
            }
        }
        if ($obj instanceof Compact) return self::dumpCompact($obj, $indent);
        //TODO:  consider dumping datetime as date strings according to a format provided by user or default
        if ($obj instanceof \DateTime) return $obj->format(self::DATE_FORMAT);
        $propList = get_object_vars($obj);
        foreach ($propList as $property => $value) {
            if (is_scalar($value) || $value instanceof Compact || $value instanceof \DateTime) {
                self::add("$property: ".self::dump($value, $indent), $indent);
            } else {
                self::add("$property:", $indent);
                self::dump($value, $indent + self::INDENT);
            }
        }
    }

    /**
     * Dumps a Compact|mixed (representing an array or object) as the single-line format representation.
     * All values inside are assumed single-line as well.
     * Note: can NOT use JSON_encode because of possible reference calls or definitions as : '&abc 123', '*fre'
     * which would be quoted by json_encode
     *
     * @param mixed   $subject The subject
     * @param integer $indent  The indent
     *
     * @return string the string representation (JSON like) of the value
     */
    public static function dumpCompact($subject, int $indent)
    {
        $pairs = [];
        if (is_array($subject) || $subject instanceof \ArrayIterator) {
            $max = count($subject);
            $objectAsArray = is_array($subject) ? $subject : $subject->getArrayCopy();
            if(array_keys($objectAsArray) !== range(0, $max-1)) {
                $pairs = $objectAsArray;
            } else {
                $valuesList = [];
                foreach ($objectAsArray as $value) {
                    $valuesList[] = is_scalar($value) ? self::dump($value, $indent) : self::dumpCompact($value, $indent);
                }
                return '['.implode(', ', $valuesList).']';
            }
        } else {
            $pairs = get_object_vars($subject);
        }
        $content = [];
        foreach ($pairs as $key => $value) {
            $content[] = "$key: ".(is_scalar($value) ? self::dump($value, $indent) : self::dumpCompact($value, $indent));
        }
        return '{'.implode(', ', $content).'}';
    }
}
