<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

/**
 *
 */
class Dumper //extends AnotherClass
{
    private $options = 00000;

    //options
    const EXPAND_SHORT = 00001;
    const SERIALIZE_CUSTOM_OBJECTS = 00010;

    public function __construct()
    {
        # code...
    }

    public static function toString($value, $options)
    {
        # code...
    }

    public static function toFile($value, $options)
    {
        # code...
    }
}