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
    /**
     *  Construct Compact according to argument if present
     *
     * @param array|object  $candidate  The candidate to be made into Compact
     */
    public function __construct($candidate = null)
    {
        $candidate = $candidate ?? [];
        parent::__construct($candidate, 1); //1 = Array indices can be accessed as properties in read/write.
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
        return $this;//new \Stdclass;
    }

    /**
     * Transforms an object/array into a new Compact object
     *
     * @param array|object $arrayOrObject the variable to mutate as Compact
     *
     * @return Compact
     * @throws \Exception if type can not be made "compact"
     */
    public static function wrap($arrayOrObject)
    {
        try {
            $out = new Compact($arrayOrObject);
        } catch (\Exception $e) {
            throw new \Exception(__METHOD__.":only array or object can be made as compact syntax", 1);
        }
        return $out;
    }

}
