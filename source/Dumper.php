<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;
use \SplDoublyLinkedList as DLL;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Dumper //extends AnotherClass
{
    private const INDENT = 2;
    private const WIDTH = 120;
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
        if (is_null($dataType)) throw new \Exception(self::class.": No content to convert to Yaml", 1);
        self::$options = is_int($options) ? $options : self::OPTIONS;
        self::$result = new DLL;
        if ($dataType instanceof YamlObject) {
            self::dumpYamlObject($dataType);
        } elseif (is_array($dataType) && $dataType[0] instanceof YamlObject) {
            array_map([self::class, 'dumpYamlObject'], $dataType);
        } else {
            self::dump($dataType, 0);
        }
        $out = implode("\n", iterator_to_array(self::$result));//var_dump(iterator_to_array(self::$result));
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
     * @return boolean  true = if the file has been correctly saved  (according to return from 'file_put_contents')
     */
    public static function toFile(string $filePath, $dataType, int $options = null):bool
    {
        return !is_bool(file_put_contents($filePath, self::toString($dataType, $options)));
    }

    private static function dump($dataType, int $indent)
    {
        if (is_scalar($dataType)) {
            if ($dataType === INF) return '.inf';
            if ($dataType === -INF) return '-.inf';
            switch (gettype($dataType)) {
                case 'boolean': return $dataType ? 'true' : 'false';
                case 'float': //fall through
                case 'double': return is_nan($dataType) ? '.nan' : sprintf('%.'.self::$floatPrecision.'F', $dataType);
                default:
                    return $dataType;
            }
        } elseif (is_object($dataType)) {
            return self::dumpObject($dataType, $indent);
        } elseif (is_array($dataType)) {
            return self::dumpArray($dataType, $indent);
        }
    }

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

    private static function add($value, $indent)
    {
        $newVal = str_repeat(" ", $indent).$value;
        foreach (str_split($newVal, self::WIDTH) as $chunks) {
            self::$result->push($chunks);
        }
    }

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

    private static function insertComments(array $commentsArray)
    {
        foreach ($commentsArray as $lineNb => $comment) {
            self::$result->add($lineNb, $comment);
        }
    }

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
        // if ($obj instanceof \SplString) {var_dump('splstrin',$obj);return '"'.$obj.'"';}
        $propList = get_object_vars($obj);//var_dump($propList);
        foreach ($propList as $property => $value) {
            if (is_scalar($value) || $value instanceof Compact || $value instanceof \DateTime) {
                self::add("$property: ".self::dump($value, $indent), $indent);//var_dump('IS SCALAR', $value);
            } else {
                self::add("$property:", $indent);
                // self::add(self::dump($value, $indent + self::INDENT), $indent + self::INDENT);var_dump('NOT SCALAR');
                self::dump($value, $indent + self::INDENT);//var_dump('NOT SCALAR');
            }
        }
    }

    public static function dumpCompact($subject, int $indent)
    {//var_dump('ICI');
        $pairs = [];
        if (is_array($subject) || $subject instanceof \ArrayIterator) {
            $max = count($subject);
            $objectAsArray = is_array($subject) ? $subject : $subject->getArrayCopy();//var_dump(array_keys($objectAsArray), range(0, $max));
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
