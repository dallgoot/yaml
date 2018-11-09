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

    /** @var null|NodeList */
    private static $result;
    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;

    // public function __construct(int $options = null)
    // {
    //     if (is_int($options)) self::$options = $options;
    // }

    /**
     * Returns the YAML representation as a string of the $dataType provided
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
     * @return boolean  true = if the file has been correctly saved  (according to return from 'file_put_contents')
     */
    public static function toFile(string $filePath, $dataType, int $options = null):bool
    {
        return !is_bool(file_put_contents($filePath, self::toString($dataType, $options)));
    }

    private static function dump($dataType, int $indent)
    {
        if (is_scalar($dataType)) {
            switch (gettype($dataType)) {
                case 'boolean': return $dataType ? 'true' : 'false';
                case 'float': if (is_infinite($dataType)) return $dataType > 0 ? '.inf' : '-.inf';
                              return sprintf('%.2F', $dataType);
                case 'double': if (is_nan((float) $dataType)) return '.nan';
                default:
                    return $dataType;
            }
        } elseif (is_object($dataType)) {
            self::dumpObject($dataType, $indent);
        } elseif (is_array($dataType)) {
            self::dumpSequence($dataType, $indent);
        }
    }

    private static function dumpYamlObject(YamlObject $dataType)
    {
        self::$result->push("---");
        // self::dump($dataType, 0);
        if (count($dataType) > 0) {
            self::dumpSequence($dataType->getArrayCopy(), 0);
        } else {
            self::dumpObject($dataType, 0);
        }
        // self::insertComments($dataType->getComment());
        //TODO: $references = $dataType->getAllReferences();
    }

    private static function add($value, $indent)
    {
        $newVal = str_repeat(" ", $indent).$value;
        foreach (str_split($newVal, self::WIDTH) as $chunks) {
            self::$result->push($chunks);
        }
    }

    private static function dumpSequence(array $array, int $indent)
    {
        $refKeys = range(0, count($array));
        foreach ($array as $key => $item) {
            $lineStart = current($refKeys) === $key ? "- " : "- $key: ";
            if (is_scalar($item)) {
                self::add($lineStart.$item, $indent);
            } else {
                self::add($lineStart, $indent);
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
        if ($obj instanceof \DateTime) return $obj->format('Y-m-d');
        $propList = get_object_vars($obj);
        foreach ($propList as $property => $value) {
            if (is_scalar($value)) {
                self::add("$property: ".self::dump($value, $indent), $indent);
            } else {
                self::add("$property: ", $indent);
                self::add(self::dump($value, $indent + self::INDENT), $indent + self::INDENT);
            }
        }
    }

    public static function dumpCompact($subject, int $indent)
    {
        $pairs = [];
        if (is_array($subject) || $subject instanceof \Countable) {
            $max = count($subject);
            $objectAsArray = is_array($subject) ? $subject : $subject->getArrayCopy();
            if(array_keys($objectAsArray) !== range(0, $max)) {
                $pairs = $objectAsArray;
            } else {
                $valuesList = array_map([self, 'dump'], $objectAsArray, array_fill( 0 , $max , $indent ));
                return '['.implode(', ', $valuesList).']';
            }
        } else {
            $pairs = get_object_vars($subject);
        }
        $content = [];
        foreach ($pairs as $key => $value) {
            if (is_scalar($value)) {
                $content[] = "$key: ".self::dump($value, $indent);
            } else {
                $content[] = "$key: ".self::dumpCompact($value, $indent);
            }
        }
        return '{'.implode(', ', $content).'}';
    }
}
