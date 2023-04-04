<?php

namespace Dallgoot\Yaml\Types;

// use \ReflectionMethod as RM;

/**
 * The Yaml\Tag class is an object type that encapsulates current
 * value which is tagged but no methods have been declared
 * by user (or standard) to transform it.
 * To register a method (Closure) for a specfici tag see Yaml\TagFactory
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 * @see     TagFactory
 */
final class Tagged
{
    public string $tagName;
    public mixed $value;

    private const NO_NAME = '%s Error: a tag MUST have a name';

    public function __construct(string $tagName, mixed $value)
    {
        if (empty($tagName)) {
            throw new \Exception(sprintf(self::NO_NAME, __METHOD__));
        }
        $this->tagName = $tagName;
        $this->value   = $value;
    }

    /**
     * Should verify if the tag is correct
     *
     * @param string $providedName The provided name
     * @todo  is this required ???
     */
    // private function checkNameValidity(string $providedName)
    // {
    /* TODO  implement and throw Exception if invalid (setName method ???)
         *The suffix must not contain any “!” character. This would cause the tag shorthand to be interpreted as having a named tag handle. In addition, the suffix must not contain the “[”, “]”, “{”, “}” and “,” characters. These characters would cause ambiguity with flow collection structures. If the suffix needs to specify any of the above restricted characters, they must be escaped using the “%” character. This behavior is consistent with the URI character escaping rules (specifically, section 2.3 of RFC2396).

         regex (([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
        */
    // }
}
