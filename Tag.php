<?php
namespace Dallgoot\Yaml;

/**
 *
 */
class Tag
{
    /** @var string */
    public $tagName;
    /** @var Node|null|string */
    public $value;

    public function __construct($tagName, $value)
    {
        if (is_null($tagName)) {
            throw new \Exception(self::class.": a tag MUST have a name", 1);
        }
        $this->tagName  = $tagName;
        $this->value = $value;
    }

    // public function __toString()
    // {
    //     return $this->value;
    // }
}
