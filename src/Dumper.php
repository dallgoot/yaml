<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types\YamlObject;

/**
 *  Convert PHP datatypes to a YAML string syntax
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Dumper
{
    public const INDENT = 2;
    // private const WIDTH  = 120; //TODO forget this feature for the moment
    private const OPTIONS = 0b00000;
    public const DATE_FORMAT = 'Y-m-d';

    public $options;
    //options
    public const EXPAND_SHORT = 0b00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 0b00010;
    public const USE_TILDE_AS_NULL = 0b00100;

    public int $floatPrecision = 4;

    private bool $multipleDocs = false;

    public bool $_compactMode = false;

    private ?DumperHandlers $handler;

    public const KEY_MASK_SEQ = '- %s';
    public const KEY_MASK_MAP = '%s: %s';

    public function __construct($options = null)
    {
        $this->options = is_int($options) ? $options : self::OPTIONS;
        $this->handler = new DumperHandlers($this);
    }
    /**
     * Returns (as a string) the YAML representation of the $dataType provided
     *
     * @param mixed    $dataType The data type
     *
     * @throws \Exception datatype cannot be null
     *
     * @return string The Yaml string content
     */
    public function toString($dataType, ?int $options = null): string
    {
        if (empty($dataType)) throw new \Exception(self::class . ": No content to convert to Yaml");
        if (is_scalar($dataType)) {
            return "--- " . $this->handler->dumpScalar($dataType) . PHP_EOL;
        }
        return $this->dump($dataType, 0, false, true);
    }

    /**
     * Calls and saves the result of Dumper::toString to the file $filePath provided
     *
     * @param mixed    $dataType The data type
     *
     * @throws \Exception datatype cannot be null
     *
     * @return bool true = if the file has been correctly saved  ( return value from 'file_put_contents')
     */
    public function toFile(string $filePath, $dataType, ?int $options = null): bool
    {
        return !is_bool(file_put_contents($filePath, $this->toString($dataType, $options)));
    }



    public function dump(mixed $dataType, int $indent, bool $isCompact = false, $isRoot = false): string
    {
        return match(true) {
            $dataType instanceof YamlObject => $this->dumpYamlObject($dataType),
            is_null($dataType) => $this->options & self::USE_TILDE_AS_NULL ? '~' : '',
            is_scalar($dataType) => $this->handler->dumpScalar($dataType),
            is_array($dataType) => $this->handler->dumpArray($dataType, $indent, $isCompact, $isRoot),
            is_object($dataType) => $this->handler->dumpObject($dataType, $indent, $isCompact, $isRoot),
            is_resource($dataType) => get_resource_type($dataType),
            is_callable($dataType, false, $callable_name) => $callable_name,
            default => '[Unknown Type]',
        };
    }


    public function dumpMultiDoc(array $arrayOfYamlObject): string
    {
        $docs = [];
        foreach ($arrayOfYamlObject as $yamlObject) {
            $docs[] = $this->dumpYamlObject($yamlObject);
        }
        return "---\n" . implode("\n---\n", $docs);
    }

    /**
     * Dumps an yaml object to a YAML string
     *
     * @todo  export comment from YamlObject
     */
    public function dumpYamlObject(YamlObject $obj): string
    {
        if ($this->multipleDocs || $obj->hasDocStart() || $obj->isTagged()) {
            $this->multipleDocs = true;
            // && $this->$result instanceof DLL) $this->$result->push("---");
        }
        // $this->insertComments($obj->getComment());
        $properties = get_object_vars($obj);
        $pairs = [];
        if (count($properties) === 0) {
            return $this->handler->dumpArray($obj->getArrayCopy(), 0, false, true);
        }else {
            return $this->handler->dumpObject($obj, 0, false, true);
        }
    }


    // public function iteratorToString(
    //     $compound,
    //     string $keyMask,
    //     string $itemSeparator,
    //     int $indent,
    //     bool $compact = false
    // ): string {
    //     $pairs = [];
    //     $valueIndent = $indent + self::INDENT;
    //     if(is_object($compound)) {
    //         $compound = get_object_vars($compound);
    //     }
    //     foreach ($compound as $key => $value) {
    //         $separator = "\n";
    //         if (is_scalar($value) || $value instanceof \DateTime) {
    //             $separator   = ' ';
    //             $valueIndent = 0;
    //         }
    //         if ($compact) {
    //             $pairs[] = sprintf($keyMask, $key) . $this->dump($value, $valueIndent);
    //         } else {
    //             $pairs[] = str_repeat(' ', $indent) . sprintf($keyMask, $key) . $separator . $this->dump($value, $valueIndent);
    //         }
    //     }
    //     return implode($itemSeparator, $pairs);
    // }
}
