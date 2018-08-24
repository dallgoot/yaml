<?php

namespace Dallgoot\Yaml;


/**
 *
 * @author stephane.rebai@gmail.com
 * @license Apache 2.0
 * @link TODO : url to specific online doc
 */
class Compact extends \ArrayIterator implements \JsonSerializable
{
    // private static $value;
    //    public function __construct($argument)
    //    {
    //     self::$value = $argument;
    //        // if ($argument instanceof \Countable && count($argument) > 0) {
    //        //     # it's an array-like
    //        // } else {
    //        //     //it's an object-like
    //        // }
    //    }
    //
    public function __construct()
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
    }

    public function jsonSerialize()
    {
        $prop = get_object_vars($this);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
    }

    public static function wrap($arrayOrObject)
    {
        $out = new Compact;
        if (is_array($arrayOrObject) || is_subclass_of($arrayOrObject, 'Iterator')) {
            foreach ($arrayOrObject as $key => $value) {
                $out[$key] = $value;
            }
        } elseif (is_object($arrayOrObject)) {
            $propList = get_object_vars($arrayOrObject);
            foreach ($propList as $prop => $value) {
                $out->{$prop} = $value;
            }
        } else {
            throw new \Exception(__METHOD__.":only array or object can be made as compact syntax", 1);
        }
        return $out;
    }

    // public function __toString()
    // {
    //     $max = count($this);
    //     $pairs = [];
    //     if ($max > 0) {
    //         $objectAsArray = $this->getArrayCopy();
    //         if(array_keys($objectAsArray) !== range(0, $max)) {
    //             $pairs = $objectAsArray;
    //         } else {
    //             $valuesList = array_map([self, 'dump'], $objectAsArray, array_fill( 0 , $max , $indent ));
    //             return '['.implode(', ', $valuesList).']';
    //         }
    //     } else {
    //         $pairs = get_object_vars($this);
    //     }
    //     $content = [];
    //     foreach ($pairs as $key => $value) {
    //         $content[] = "$key: ".Dumper::dump($value, $indent);
    //     }
    //     // var_dump('ccc', $pairs);
    //     return '{'.implode(', ', $content).'}';
    // }
}
