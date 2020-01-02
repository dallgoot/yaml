<?php
namespace Dallgoot\Yaml;


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
    public const INDENT = 2;
    // private const WIDTH  = 120; //TODO forget this feature for the moment
    private const OPTIONS = 0b00000;
    public const DATE_FORMAT = 'Y-m-d';

    public $options;
    //options
    public const EXPAND_SHORT = 0b00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 0b00010;
    /** @var int */
    public $floatPrecision = 4;
    /** @var bool */
    private $multipleDocs = false;
    /** @var bool */
    public $_compactMode = false;
    /** @var null|DumperHandlers */
    private $handler;


    public function __construct($options=null)
    {
        $this->options = is_int($options) ? $options : self::OPTIONS;
        $this->handler = new DumperHandlers($this);
    }
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
    public function toString($dataType, int $options = null):string
    {
        if (empty($dataType)) throw new \Exception(self::class.": No content to convert to Yaml");
        if (is_scalar($dataType)) {
            return "--- ".$this->handler->dumpScalar($dataType). self::LINEFEED ;
        }
        return $this->dump($dataType, 0);
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
    public function toFile(string $filePath, $dataType, int $options = null):bool
    {
        return !is_bool(file_put_contents($filePath, $this->toString($dataType, $options)));
    }



    public function dump($dataType, int $indent):string
    {
        if (is_null($dataType)) {
            return '';
        } elseif (is_resource($dataType)) {
            return get_resource_type($dataType);
        } elseif (is_scalar($dataType)) {
            return $this->handler->dumpScalar($dataType);
        } else {
            return $this->handler->dumpCompound($dataType, $indent);
        }
    }


    public function dumpMultiDoc($arrayOfYamlObject)
    {
        $output = '';
        foreach ($arrayOfYamlObject as $key => $yamlObject) {
            $output .= "---\n".$this->dumpYamlObject($yamlObject)."\n";
        }
        return $output;
    }

    /**
     * Dumps an yaml object to a YAML string
     *
     * @param      YamlObject  $obj    The object
     *
     * @return     string      YAML formatted string
     * @todo  export comment from YamlObject
     */
    public function dumpYamlObject(YamlObject $obj):string
    {
        if ($this->multipleDocs || $obj->hasDocStart() || $obj->isTagged()) {
           $this->multipleDocs = true;
          // && $this->$result instanceof DLL) $this->$result->push("---");
        }
        // $this->insertComments($obj->getComment());
        if (count($obj) > 0) {
            return $this->iteratorToString($obj, '-', "\n", 0);
        } else {
            return $this->iteratorToString(new \ArrayIterator(get_object_vars($obj)), '%s:', "\n", 0);
        }
    }


    public function iteratorToString(\Iterator $iterable,
                                      string $keyMask, string $itemSeparator, int $indent):string
    {
        $pairs = [];
        $valueIndent = $indent + self::INDENT;
        foreach ($iterable as $key => $value) {
            $separator = "\n";
            if (is_scalar($value) || $value instanceof Compact || $value instanceof \DateTime) {
                $separator   = ' ';
                $valueIndent = 0;
            }
            if ($this->_compactMode) {
                $pairs[] = sprintf($keyMask, $key).$this->dump($value, $valueIndent);
            } else {
                $pairs[] = str_repeat(' ', $indent).sprintf($keyMask, $key).$separator.$this->dump($value, $valueIndent);
            }

        }
        // $processItem = function ()
        return implode($itemSeparator, $pairs);
        // return implode($itemSeparator, array_map(callback, arr1));
    }
}
