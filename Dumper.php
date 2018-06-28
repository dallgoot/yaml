<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

/**
 *
 */
class Dumper //extends AnotherClass
{
    private $options = 00000;
    private $content = null;

    //options
    const EXPAND_SHORT = 00001;
    const SERIALIZE_CUSTOM_OBJECTS = 00010;

    public function __construct($candidate = null, $options = null)
    {
        if (!is_null($options)) {
            $this->options = $options;
        }
        if (!is_null($candidate)) {
            $this->content = $candidate;
        }
    }

    public static function toString($value, $options)
    {
        if (is_null($value) && is_null($this->content)) {
            throw new \Exception("No content to convert to Yaml", 1);
        }
        $value = is_null($value) ? $this->content : $value;
        $options = is_null($options) ? $this->options : $options;
    }

    public static function toFile($file, $value, $options)
    {
        if (!is_null($value)) {
            $string = $this->toString($value, $options);
        } elseif (!is_null($this->content)) {
            $string = $this->toString($this->content, $options);
        }
        return !is_bool(file_put_contents($file, $string));
    }
}
