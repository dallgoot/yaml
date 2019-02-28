<?php
namespace Dallgoot\Yaml;

use \ReflectionMethod as RM;
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
    /** @var mixed */
    public $value;

    private const NO_NAME = '%s Error: a tag MUST have a name';

    /**
     * Tag constructor.
     *
     * @param string $tagName the name of the tag like '!!str' (WITHOUT the first "!")
     * @param mixed  $raw     any PHP variable type
     *
     * @throws \Exception if $tagName is an invalid string or absent
     */
    public function __construct(string $tagName, $value)
    {
        if (empty($tagName)) {
            throw new \Exception(sprintf(self::NO_NAME, __METHOD__));
        }
        $this->tagName = $tagName;
        $this->value   = $value;
    }
}