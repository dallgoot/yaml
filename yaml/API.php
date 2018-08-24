<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Yaml as Y;

/**
 * TODO
 * @author stephane.rebai@gmail.com
 * @license Apache 2.0
 * @link TODO : url to specific online doc
 */
class API
{
    private $_references = [];
    private $_comments   = [];
    // private $_documents  = [];
    private $_tags = [];

    public $type = Y::MAPPING;
    public $value = null;

    const UNKNOWN_REFERENCE = self::class.": no reference named '%s'";
    const UNAMED_REFERENCE  = self::class.": reference MUST have a name";


    /**
     * Adds a reference.
     * @param string  $name   The reference name
     * @param mixed    $value  The reference value
     *
     * @throws \UnexpectedValueException  (description)
     */
    public function addReference(string $name, $value)
    {
        if (empty($name)) {
            throw new \UnexpectedValueException(self::UNAMED_REFERENCE, 1);
        }
        $this->_references[(string) $name] = $value;
    }

    /**
     *  return the reference saved by $name
     * @param  string  $name Name of the reference
     *
     * @return mixed Value of the reference
     * @throws UnexpectedValueException    if there's no reference by that $name
     */
    public function &getReference($name)
    {
        if (array_key_exists($name, $this->_references)) {
            return $this->_references[$name];
        }
        throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE, $name), 1);
    }

    public function getAllReferences():array
    {
        return $this->_references;
    }

    /**
     * Adds a comment.
     *
     * @param int $lineNumber      The line number at which thecomment should appear
     * @param string $value      The comment
     */
    public function addComment(int $lineNumber, $value)
    {
        $this->_comments[$lineNumber] = $value;
    }

    /**
     * Gets the comment at $lineNumber
     *
     * @param int|null  $lineNumber  The line number
     *
     * @return string|array The comment à $lineNumber OR ALL comments.
     */
    public function getComment(int $lineNumber = null)
    {
        if (array_key_exists((int) $lineNumber, $this->_comments)) {
            return $this->_comments[$lineNumber];
        }
        return $this->_comments;
    }

    /**
     * Sets the text when the content is *only* a litteral
     *
     * @param string  $value  The value
     */
    public function setText(string $value)
    {
        $this->value .= $value;
    }

    /**
     * TODO:  what to do with these tags ???
     * Adds a tag.
     *
     * @param      string  $value  The value
     */
    public function addTag(string $value)
    {
        $this->_tags[] = $value;
    }
}
