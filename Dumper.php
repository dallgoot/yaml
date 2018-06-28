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

    public function __construct($candidate = null, $options = null)
    {
        if (!is_null($options)) {
            $this->options = $options;
        }
    }

    public static function toString($value, $options)
    {
        if (is_null($value)) {
            throw new \Exception("No content to convert to Yaml", 1);
        }
        $options = is_null($options) ? self::options : $options;
    }

    public static function toFile($file, $value, $options)
    {
        return !is_bool(file_put_contents($file, self::toString($value, $options)));
    }
}
