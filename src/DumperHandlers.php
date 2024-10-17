<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Dumper;
use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Types\Compact;
use Dallgoot\Yaml\Types\Tagged;

/**
 *  Convert PHP datatypes to a YAML string syntax
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class DumperHandlers
{
    private $dumper;

    public function __construct(Dumper $dumper)
    {
        $this->dumper = $dumper;
    }


    public function dumpScalar($dataType): string
    {
        if ($dataType === \INF) return '.inf';
        if ($dataType === -\INF) return '-.inf';
        $precision = "%." . $this->dumper->floatPrecision . "F";
        switch (gettype($dataType)) {
            case 'boolean':
                return $dataType ? 'true' : 'false';
            case 'float': //fall through
            case 'double':
                return is_nan((float) $dataType) ? '.nan' : sprintf($precision, $dataType);
        }
        return $this->dumpString($dataType);
    }


    // public function dumpCompound($compound, int $indent, bool $compact=false): string
    // {
    //     if ($compact) {
    //         return $this->dumpCompact($compound, $indent);
    //     } else {
    //         if (is_array($compound)) {
    //             if (isset($compound[0]) && $compound[0] instanceof YamlObject) {
    //                 return $this->dumper->dumpMultiDoc($compound);
    //             }
    //             $keyMask = '-';
    //             $refKeys = range(0, count($compound) - 1);
    //             if (array_keys($compound) !== $refKeys) {
    //                 $keyMask = '%s:';
    //             }
    //             return $this->dumper->iteratorToString($compound, $keyMask, "\n", $indent);
    //         } elseif (is_object($compound) && !is_callable($compound)) {
    //             return $this->dumpObject($compound, $indent);
    //         }
    //     }
    //     throw new \Exception("Dumping Callable|Resource is not currently supported", 1);
    // }

    public function dumpObject(object $object, int $indent, bool $isCompact = false, bool $isRoot = false): string
    {
        return match ($object::class) {
            Compact::class => $this->dumpCompact($object, $indent),
            Tagged::class => $this->dumpTagged($object, $indent),
            \DateTime::class => $this->dumpDateTime($object),
            default => $this->_object($object, $indent, $isCompact, $isRoot),
        };
    }


    public function dumpCompact(Compact $compact, int $indent, bool $isRoot = false)
    {
        $arr = $compact->getArrayCopy();
        if (count(get_object_vars($compact)) === 0) {
            return $this->dumpArray($arr, $indent, true);
        }
        return $this->_objectCompact($compact, $indent, $isRoot);
    }


    public function _object(object $o, int $indent, bool $isCompact = false, $isRoot = false): string
    {
        return $isCompact ? $this->_objectCompact($o, $indent, $isRoot)
                          : $this->_objectStd($o, $indent, false, $isRoot);
    }


    public function _objectStd(object $o, int $indent, bool $isCompact = false, bool $isRoot = false)
    {
        $pairs = [''];
        $realIndent = $indent + Dumper::INDENT;
        if($isRoot) {
            $pairs = [];
            $realIndent = 0;
        }
        foreach (get_object_vars($o) as $key => $value) {
            $dumpedValue = $this->dumper->dump($value, $realIndent, $value instanceof Compact, false);
            $pairs[] = sprintf("%s%s: %s", str_repeat(' ', $realIndent), $key, $dumpedValue);
        }
        return implode(PHP_EOL, $pairs);
    }


    public function _objectCompact(object $o, int $indent, bool $isRoot = false)
    {
        $pairs = [];
        foreach ($o as $key => $value) {
            $pairs[] = "$key: " . $this->dumper->dump($value, 0, true, false);
        }
        return '{'. implode(', ', $pairs) . '}';
    }


    public function dumpArray(array $a, int $indent, bool $isCompact = false, $isRoot = false): string
    {
        if(isset($a[0]) && $a[0] instanceof YamlObject) {
            return $this->dumper->dumpMultiDoc($a);
        }
        if (array_keys($a) !== range(0, count($a) - 1)) {
            return $this->_object((object) $a, $indent, $isCompact, $isRoot);
        }
        return $isCompact ? $this->_dumpCompactArray($a, $indent)
                          : $this->_dumpNativeArray($a, $indent, $isRoot);
    }


    public function _dumpNativeArray(array $a, int $indent, $isRoot = false): string
    {
        $pairs = [''];
        $realIndent = $indent + Dumper::INDENT;
        if($isRoot) {
            $pairs = [];
            $realIndent = 0;
        }
        foreach ($a as $value) {
            $dumpedValue = $this->dumper->dump($value, 0, $value instanceof Compact, false);
            // TODO : seems ok but make tests to double check : fixed by PR from Delkano
            $dumpedValue = $this->dumper->dump($value, $realIndent, $value instanceof Compact, false);
            $pairs[] = sprintf("%s- %s", str_repeat(' ', $realIndent), $dumpedValue);
        }
        return implode(PHP_EOL, $pairs);
    }


    public function _dumpCompactArray(array $a, int $indent): string
    {
        $pairs = [];
        foreach ($a as $value) {
            $pairs[] = $this->dumper->dump($value, $indent, true);
        }
        return '[' . implode(', ', $pairs) . ']';
    }


    public function dumpDateTime(\DateTime $datetime): string
    {
        return $datetime->format($this->dumper::DATE_FORMAT);
    }

    
    /**
     * Dumps a string. Protects it if needed
     *
     * @param      string  $str    The string
     *
     * @return     string  ( description_of_the_return_value )
     * @todo   implements checking and protection function
     */
    public function dumpString(string $str): string
    {
        //those characters must be escaped : - : ? { } [ ] # , & * ! > | ' " %
        // The “@” (#x40, at) and “`” (#x60, grave accent) are reserved for future use.
        // 5.4. Line Break Characters
        // Example 5.13. Escaped Characters

        $str = json_encode(ltrim($str));
        return strspn(substr($str, 1, -1), "-:?{}[]#,&*!>|'\"%") > 0 ? $str : trim($str, '"');
    }

    //TODO : handle 'php/object'
    public function dumpTagged(Tagged $obj, int $indent): string
    {
        $separator = "\n";
        $isCompact = $obj->value instanceof Compact;
        if (is_scalar($obj->value) || $isCompact) {
            $separator   = ' ';
        }
        return ($obj->tagName) . $separator . $this->dumper->dump($obj->value, $indent, $isCompact);
    }


}
