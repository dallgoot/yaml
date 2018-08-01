<?php

namespace Dallgoot\Yaml;


/**
 *
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
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
        parent::__construct([], 1);//1 = Array indices can be accessed as properties in read/write.
    }

    public function jsonSerialize()
    {
        $prop = get_object_vars($this);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
    }

    public static function wrap($arrayOrObject)
    {
        //
        return self;
    }
}
