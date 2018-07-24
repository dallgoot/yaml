<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Types as T, YamlObject, Tag, Compact};
use \SplDoublyLinkedList as DLL;

/**
 *
 */
class Dumper //extends AnotherClass
{
    private const indent = 2;
    private const width = 120;
    private const options = 00000;

    private static $result;
    private static $options;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;

    public function __construct(int $options = null)
    {
        if (is_int($options)) self::$options = $options;
    }

    public static function toString($dataType, int $options):string
    {
        if (is_null($dataType)) throw new \Exception(self::class.": No content to convert to Yaml", 1);
        self::$options = is_int($options) ? $options : self::$options;
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

    public static function toFile(string $filePath, $dataType, int $options):bool
    {
        return !is_bool(file_put_contents($filePath, self::toString($dataType, $options)));
    }

    private static function dump($dataType, int $indent)
    {
        if (is_scalar($dataType)) {
            switch (gettype($dataType)) {
                case 'boolean': return $dataType ? 'true' : 'false';break;
                case 'float': if (is_infinite($dataType)) return $dataType > 0 ? '.inf' : '-.inf';
                case 'double': if (is_nan($dataType)) return '.nan';
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
        return self::dump($dataType, 0);
        // self::insertComments($dataType->getComment());
        //TODO: $references = $dataType->getAllReferences();
    }

    private static function add($value, $indent)
    {
        $newVal = str_repeat(" ", $indent).$value;
        foreach (str_split($newVal, self::width) as $chunks) {
            self::$result->push($chunks);
        }
    }

    private static function dumpSequence(array $array, int $indent):void
    {
        $refKeys = range(0, count($array));
        foreach ($array as $key => $item) {
            $lineStart = current($refKeys) === $key ? "- " : "- $key: ";
            if (is_scalar($item)) {
                self::add($lineStart.$item, $indent );
            } else {
                self::add($lineStart, $indent );
                self::dump($item, $indent + self::indent);
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

    private function dumpObject(object $object, int $indent)
    {
        if ($dataType instanceof Tag) {
            if (is_scalar($dataType->value)) {
                return "!".$dataType->tagName.' '.$dataType->value;
            } else{
                yield "!".$dataType->tagName;
                self::dump($dataType->value, $indent + self::indent);
            }
        }
        if ($dataType instanceof Compact) {//TODO
            self::dumpCompact($dataType, $indent);
        }
        if ($dataType instanceof \DateTime) {
            # code...
        }
        $propList = get_object_vars($dataType);
        foreach ($propList as $property => $value) {
            if (is_scalar($value)) {
                self::add("$property: ".$value, $indent);
            } else {
                self::add("$property: ", $indent);
                self::dump($value, $indent + self::indent);
            }
        }
    }

    public static function dumpCompact(Compact $object, int $indent)
    {
        // if (empty($object)) return "{}";
        // if (empty($object)) return "[]";
    }
}
