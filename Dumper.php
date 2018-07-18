<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Types as T, YamlObject, Tag, Compact};
use \SpldDoublyLinkedList as DLL;

/**
 *
 */
class Dumper //extends AnotherClass
{
    private const indent = 2;
    private const width = 160;
    private const options = 00000;

    private static $result;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;

    public function __construct(int $options = null)
    {
        if (!is_null($options)) {
            $this->options = $options;
        }
    }

    public static function toString($dataType, int $options):string
    {
        if (is_null($dataType)) {
            throw new \Exception(self::class.": No content to convert to Yaml", 1);
        }
        $options = is_null($options) ? self::options : $options;
        self::$result = new DLL;
        self::$result->setIteratorMode(DLL::IT_MODE_FIFO | DLL::IT_MODE_DELETE);
        if ($dataType instanceof YamlObject) {
            return self::dumpYamlObject($dataType);
        } elseif (is_array($dataType) && $dataType[0] instanceof YamlObject) {
            array_map([self, dumpYamlObject], $dataType);
        } else {
            self::dump($value, 0);
        }
        return implode("\n", self::$result);
    }

    public static function toFile(string $file, $dataType, int $options):bool
    {
        return !is_bool(file_put_contents($file, self::toString($dataType, $options)));
    }

    private static function dump($dataType, int $indent)
    {
        if (is_object($dataType)) {
            if ($dataType instanceof Tag) {
                foreach (self::split("!".$dataType->tagName.' '.$dataType->value, $indent) as $chunks) {
                    self::$result->push($chunks);
                }
            }
            if ($dataType instanceof Compact) {
                self::dumpCompact($dataType, $indent);
            }
            if ($dataType instanceof \DateTime) {
                # code...
            }
        } elseif (is_array($dataType)) {
            self::dumpSequence($dataType, $indent);
        }
    }

    private static function dumpYamlObject(YamlObject $dataType)
    {
        self::dump($dataType, 0);
        self::insertComments($dataType->getComment());
        //TODO: $references = $dataType->getAllReferences();
    }

    private static function split($string, $indent):array
    {
        return str_split(str_pad($string, $indent, " ", STR_PAD_LEFT), self::width);
    }

    private static function dumpSequence(array $array, int $indent):void
    {
        $refKeys = range(0, count($array));
        foreach ($array as $key => $item) {
            $lineStart = current($refKeys) === $key ? "- " : "- $key: ";
            $value     = self::dump($item, $indent + self::indent);
            if (is_null($value)) {
                self::$result->push(self::split($lineStart, $indent + self::indent)[0]);
            } else {
                foreach (self::split($lineStart.$value, $indent + self::indent) as $chunks) {
                    self::$result->push($chunks);
                }
            }
            next($refKeys);
        }
    }

    private static function insertComments($commentsArray):void
    {
        foreach ($commentsArray as $lineNb => $comment) {
            self::$result->add($lineNb, $comment);
        }
    }

    public static function dumpCompact($value)
    {
        # code...
    }
}
