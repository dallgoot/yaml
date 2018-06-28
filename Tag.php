<?php
namespace Dallgoot\Yaml;

/**
 *
 */
class Tag
{
    public $name;
    public $value;

    public function __construct($name, $value)
    {
        if (is_null($name)) {
            throw new \Exception(self::class.": a tag MUST have a name", 1);
        }
        $this->name = $name;
        $this->value =$value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
