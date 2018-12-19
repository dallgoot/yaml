<?php
namespace Dallgoot\Yaml;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Tag
{
    /** @var string */
    public $tagName;
    /** @var Node|null|string */
    public $value;

    /**
     * Tag constructor.
     * @param string $tagName the name of the tag like '!str' (WITHOUT the first "!")
     * @param mixed $value  any PHP variable type
     * @throws \Exception if $tagName is an invalid string or absent
     */
    public function __construct(string $tagName, $value)
    {
        if (empty($tagName)) {
            throw new \Exception(self::class.": a tag MUST have a name", 1);
        }
        $this->tagName = $tagName;
        $this->value = $value;
    }

    /**
     * Should verify if the tag is correct
     *
     * @param      string  $providedName  The provided name
     * @todo is this required ???
     */
    // private function checkNameValidity(string $providedName)
    // {
        /* TODO  implement and throw Exception if invalid (setName method ???)
         *The suffix must not contain any “!” character. This would cause the tag shorthand to be interpreted as having a named tag handle. In addition, the suffix must not contain the “[”, “]”, “{”, “}” and “,” characters. These characters would cause ambiguity with flow collection structures. If the suffix needs to specify any of the above restricted characters, they must be escaped using the “%” character. This behavior is consistent with the URI character escaping rules (specifically, section 2.3 of RFC2396).
        */
    // }
}
