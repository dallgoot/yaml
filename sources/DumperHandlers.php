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
class DumperHandlers
{
    private const INDENT = 2;
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
     * Dump (determine) the string value according to type
     *
     * @param string  $dataType The data type
     * @param integer $indent   The indent
     *
     * @return string The YAML representation of $dataType
     */
    public static function dump($dataType, int $indent):string
    {
        if(is_null($dataType)) {
            return '';
        } elseif(is_resource($dataType)) {
            return get_resource_type($dataType);
        } elseif (is_scalar($dataType)) {
            return self::dumpScalar($dataType, $indent);
        } else {
            return self::dumpCompound($dataType, $indent);
        }
    }

    public function dumpScalar($dataType, $indent)
    {
        if ($dataType === \INF) return '.inf';
        if ($dataType === -\INF) return '-.inf';
        switch (gettype($dataType)) {
            case 'boolean': return $dataType ? 'true' : 'false';
            case 'float': //fall through
            case 'double': return is_nan((double) $dataType) ? '.nan' : sprintf('%.'.self::$floatPrecision.'F', $dataType);
            default:
                return $dataType;
        }
        return self::dumpString($dataType, $indent);
    }

    private function dumpCompound($dataType, $indent)
    {
        $iterator = null;
        if (is_callable($dataType)) {
            throw new \Exception("Dumping Callable|Closure is not currently supported", 1);
        } elseif (is_iterable($dataType)) {
            $iterator = $dataType;
        } elseif (is_object($dataType)) {
            if ($obj instanceof Tag) {
            if ($obj instanceof Compact
            if ($obj instanceof \DateTime)
            return self::dumpArray($dataType, $indent);
            $iterator = new ArrayIterator();
        }
    }
    /**
     * Dumps an YamlObject (YAML document) as a YAML string
     *
     * @param YamlObject $obj The object
     */
    private static function dumpYamlObject(YamlObject $obj)
    {
        if ($this->multipleDocs || $obj->hasDocStart() || $obj->isTagged() || $obj->isScalar()) {
           $this->multipleDocs = true;
          // && self::$result instanceof DLL) self::$result->push("---");
        }
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

    /**
     * Dumps a string. Protects it if needed
     *
     * @param      string  $str    The string
     *
     * @return     string  ( description_of_the_return_value )
     * @todo   implements checking and protection function
     */
    public static function dumpString(string $str):string
    {
        return $str;
    }
}
