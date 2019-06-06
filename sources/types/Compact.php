<?php

namespace Dallgoot\Yaml;

/**
 * This a type that encapsulates a mapping or sequence that are declared as compact/hosrt notation
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
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
        //ArrayIterator options --> Array indices can be accessed as properties in read/write.
        parent::__construct(/** @scrutinizer ignore-type */ $candidate, \ArrayIterator::STD_PROP_LIST|\ArrayIterator::ARRAY_AS_PROPS);
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
