<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

/**
 *
 */
class Compact extends ArrayIterator
{

    public function __construct($argument)
    {
        if ($argument instanceof Countable && count($argument) > 0) {
            # it's an array-like
        } else {
            //it's an object-like
        }
    }
}
