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
     * @param array|object $candidate The candidate to be made into Compact
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
        return count($prop) > 0 ? $prop : iterator_to_array($this);
    }
}
