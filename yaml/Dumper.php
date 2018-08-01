<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;
use \SplDoublyLinkedList as DLL;

/**
 *
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 */
class Dumper //extends AnotherClass
{
    private const INDENT = 2;
    private const WIDTH = 120;
    private const OPTIONS = 00000;

    private static $result;
    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;

    public function __construct(int $options = null)
    {
        if (is_int($options)) self::$options = $options;
    }

    /**
     * Returns the YAML representation as a string of the $dataType provided
     *
     * @param mixed      $dataType     The data type
     * @param int|null     $options      The options
     *
     * @throws     \Exception  datatype cannot be null
     *
     * @return     string      The Yaml string content
     */
    public static function toString($dataType, int $options = null):string
    {
        if (is_null($dataType)) throw new \Exception(self::class.": No content to convert to Yaml", 1);
        self::$options = is_int($options) ? $options : self::OPTIONS;
        self::$result = new DLL;
        self::$result->setIteratorMode(DLL::IT_MODE_FIFO | DLL::IT_MODE_DELETE);
        if ($dataType instanceof YamlObject) {
            self::dumpYamlObject($dataType);
        } elseif (is_array($dataType) && $dataType[0] instanceof YamlObject) {
            array_map([self::class, 'dumpYamlObject'], $dataType);
        } else {
            self::dump($dataType, 0);
        }
        $out = '';
        foreach (self::$result as $value) {
            $out .= $value."\n";
        }
        self::$result = null;
        return $out;
    }

    /**
     * Calls and saves the result of Dumper::toString to the file $filePath provided
     *
     * @param string   $filePath      The file path
     * @param mixed   $dataType      The data type
     * @param int|null  $options      The options
     *
     * @return     boolean  true = if the file has been correctly saved  (according to return from 'file_put_contents')
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
                case 'float': if (is_infinite((float) $dataType)) return $dataType > 0 ? '.inf' : '-.inf';
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
        self::dump($dataType, 0);
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

    private static function dumpSequence(array $array, int $indent):void
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

    private static function insertComments(array $commentsArray):void
    {
        foreach ($commentsArray as $lineNb => $comment) {
            self::$result->add($lineNb, $comment);
        }
    }

    private static function dumpObject(object $object, int $indent)
    {
        if ($object instanceof Tag) {
            if (is_scalar($object->value)) {
                return "!".$object->tagName.' '.$object->value;
            } else {
                yield "!".$object->tagName;
                self::dump($object->value, $indent + self::INDENT);
            }
        }
        if ($object instanceof Compact) {//TODO
            self::dumpCompact($object, $indent);
        }
        //TODO:  consider dumping datetime as date strings according to a format provided by user or default
        if ($object instanceof \DateTime) {
            # code...
        }
        $propList = get_object_vars($object);
        foreach ($propList as $property => $value) {
            if (is_scalar($value)) {
                self::add("$property: ".$value, $indent);
            } else {
                self::add("$property: ", $indent);
                self::dump($value, $indent + self::INDENT);
            }
        }
    }

    public static function dumpCompact(Compact $object, int $indent)
    {
        // if (empty($object)) return "{}";
        // if (empty($object)) return "[]";
    }
}
