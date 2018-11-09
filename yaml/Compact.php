<?php

namespace Dallgoot\Yaml;


/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Compact extends \ArrayIterator implements \JsonSerializable
{

    public function __construct()
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
    }

    /**
     * Provides the correct ouput for Json Serialization
     * 
     * @return array
     */
    public function jsonSerialize():array
    {
        $prop = get_object_vars($this);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
    }

    /**
     * Transforms an object/array into a new Compact object
     * 
     * @return Compact
     * @throws \Exception if type can not be made "compact"
     */
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

}
