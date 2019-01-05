<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y};
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
    /** @var null|Node|NodeList */
    private $raw;

    const LEGACY_TAGS = ['!str', '!binary', '!set', '!omap', 'php/object', '!inline', '!long'];

    /**
     * Tag constructor.
     * @param string $tagName the name of the tag like '!!str' (WITHOUT the first "!")
     * @param mixed $value  any PHP variable type
     * @throws \Exception if $tagName is an invalid string or absent
     */
    public function __construct(string $tagName, $raw)
    {
        if (empty($tagName)) {
            throw new \Exception(self::class.": a tag MUST have a name", 1);
        }
        $this->tagName = $tagName;
        $this->raw = $raw;
    }

    /**
     * Return the tagged value according to Tag type
     *
     * @param      scalar|Node|NodeList  $value  The value
     *
     * @return     mixed
     *
     * @todo implement others legacy types if needed  + Symfony type 'php/object' (unserialize ???)
     */
    public function buildValue(object &$parent = null)
    {
        $value = $this->raw;
        if ($value instanceof Node) {
            $value = new NodeList($this->raw);
        }
        $value->forceType();
        // $parent = Builder::$_root;
        switch ($this->tagName) {
            case '!set': $value->type = Y::SET;break;
            case '!omap': $value->type = Y::SEQUENCE;break;
            // assumed to be !str,!binary
            default: $parent = null;
                     $value->type = Y::RAW;
        }
        $this->value = Builder::buildNodeList($value, $parent);
        return $this->value;
    }

    public function isKnown():bool
    {
        return in_array($this->tagName, self::LEGACY_TAGS);
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
