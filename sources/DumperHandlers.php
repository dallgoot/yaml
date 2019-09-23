<?php
namespace Dallgoot\Yaml;

// use \SplDoublyLinkedList as DLL;

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

    private $options;
    private $multipleDocs = false;
    //options
    public const EXPAND_SHORT = 00001;
    public const SERIALIZE_CUSTOM_OBJECTS = 00010;
    public $floatPrecision = 4;

    public function __construct(int $options = null)
    {
        if (is_int($options)) $this->options = $options;
    }



    public function dump($dataType, int $indent):string
    {
        if (is_null($dataType)) {
            return '';
        } elseif (is_resource($dataType)) {
            return get_resource_type($dataType);
        } elseif (is_scalar($dataType)) {
            return $this->dumpScalar($dataType);
        } else {
            return $this->dumpCompound($dataType, $indent);
        }
    }

    public function dumpScalar($dataType):string
    {
        if ($dataType === \INF) return '.inf';
        if ($dataType === -\INF) return '-.inf';
        $precision = "%.".$this->floatPrecision."F";
        switch (gettype($dataType)) {
            case 'boolean': return $dataType ? 'true' : 'false';
            case 'float': //fall through
            case 'double': return is_nan((double) $dataType) ? '.nan' : sprintf($precision, $dataType);
        }
        return $this->dumpString($dataType);
    }


    private function dumpCompound($compound, int $indent):string
    {
        if (is_array($compound)) {
            $iterator = new \ArrayIterator($compound);
            $mask = '-';
            $refKeys = range(0, count($compound) - 1);
            if (array_keys($compound) !== $refKeys) {
                $mask = '%s:';
            }
            return $this->iteratorToString($iterator, $mask, $indent);
        } elseif (is_object($compound) && !is_callable($compound)) {
            return $this->dumpObject($compound, $indent);
        }
        throw new \Exception("Dumping Callable|Resource is not currently supported", 1);
    }

    private function dumpObject(object $object, int $indent):string
    {
        if ($object instanceof YamlObject) {
            return $this->dumpYamlObject($object);
        } elseif ($object instanceof Compact) {
            return $this->dumpCompact($object, $indent);
        } elseif ($object instanceof Tagged) {
            return $this->dumpTagged($object, $indent);
        } elseif ($object instanceof \DateTime) {
            return $object->format(self::DATE_FORMAT);
        } elseif (is_iterable($object)) {
            $iterator = $object;
        } else {
            $iterator = new \ArrayIterator(get_object_vars($object));
        }
        return $this->iteratorToString($iterator, '%s:', $indent);
    }

    /**
     * Dumps an yaml object to a YAML string
     *
     * @param      YamlObject  $obj    The object
     *
     * @return     string      YAML formatted string
     * @todo  export comment from YamlObject
     */
    private function dumpYamlObject(YamlObject $obj):string
    {
        if ($this->multipleDocs || $obj->hasDocStart() || $obj->isTagged()) {
           $this->multipleDocs = true;
          // && $this->$result instanceof DLL) $this->$result->push("---");
        }
        if (count($obj) > 0) {
            return $this->iteratorToString($obj, '-', 0);
        }
        return $this->iteratorToString(new \ArrayIterator(get_object_vars($obj)), '%s:', 0);
        // $this->insertComments($obj->getComment());
    }


    private function iteratorToString(\Iterator $iterable, string $keyMask, int $indent):string
    {
        $pairs = [];
        foreach ($iterable as $key => $value) {
            $separator = "\n";
            $valueIndent = $indent + self::INDENT;
            if (is_scalar($value) || $value instanceof Compact || $value instanceof \DateTime) {
                $separator   = ' ';
                $valueIndent = 0;
            }
            $pairs[] = str_repeat(' ', $indent).sprintf($keyMask, $key).$separator.$this->dump($value, $valueIndent);
        }
        return implode("\n", $pairs);
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
    public function dumpCompact($subject, int $indent):string
    {
        $structureFormat = '{%s}';
        $keyFormat = "%s: ";
        if (!is_array($subject) && !($subject instanceof \ArrayIterator)) {
            $source = get_object_vars($subject);
        } else {
            $max = count($subject);
            $objectAsArray = is_array($subject) ? $subject : $subject->getArrayCopy();
            $source = $objectAsArray;
            if (array_keys($objectAsArray) === range(0, $max - 1)) {
                $structureFormat = '[%s]';
                $keyFormat = '';
            }
        }
        $content = [];
        foreach ($source as $key => $value) {
            $content[] = sprintf($keyFormat, $key).(is_scalar($value) ? $this->dump($value, $indent) : $this->dumpCompact($value, $indent));
        }
        return sprintf($structureFormat, implode(', ', $content));
    }

    /**
     * Dumps a string. Protects it if needed
     *
     * @param      string  $str    The string
     *
     * @return     string  ( description_of_the_return_value )
     * @todo   implements checking and protection function
     */
    public function dumpString(string $str):string
    {
        //those characters must be escaped : - : ? { } [ ] # , & * ! > | ' " %
        // The “@” (#x40, at) and “`” (#x60, grave accent) are reserved for future use.
        // 5.4. Line Break Characters
        // Line breaks inside scalar content must be normalized by the YAML processor. Each such line break must be parsed into a single line feed character.
        // The original line break format is a presentation detail and must not be used to convey content information.
        // Example 5.13. Escaped Characters
        // "Fun with \\
        // \" \a \b \e \f \↓
        // \n \r \t \v \0 \↓
        // \  \_ \N \L \P \↓
        // \x41 \u0041 \U00000041"

        // ---
        // "Fun with \x5C
        // \x22 \x07 \x08 \x1B \x0C
        // \x0A \x0D \x09 \x0B \x00
        // \x20 \xA0 \x85 \u2028 \u2029
        // A A A"
        $str = json_encode(ltrim($str));
        return strspn(substr($str,1,-1), "-:?{}[]#,&*!>|'\"%") > 0 ? $str : trim($str, '"');
    }

    public function dumpTagged(Tagged $obj, int $indent):string
    {
        $separator   = ' ';
        $valueIndent = 0;
        if (!is_scalar($obj->value)) {
            $separator = "\n";
            $valueIndent = $indent + self::INDENT;
        }
        return $obj->tagName.$separator.$this->dump($obj->value, $valueIndent);
    }
}
