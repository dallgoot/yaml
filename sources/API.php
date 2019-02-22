<?php

namespace Dallgoot\Yaml;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class API
{
    /** @var null|bool */
    private $_hasDocStart; // null = no docstart, true = docstart before document comments, false = docstart after document comments
    /** @var null|YamlObject */
    private $_obj;
    private $_references = [];
    private $_comments   = [];
    // private $_documents  = [];
    private $_tags = [];
    /** @var null|int */
    // public $type = Y::MAPPING;
    /** @var null|string */
    public $value;

    const UNKNOWN_REFERENCE = "no reference named: '%s'";
    const UNAMED_REFERENCE  = "reference MUST have a name";

    /**
     * Creates API object to be used for the document provided as argument
     *
     * @param YamlObject $obj the YamlObject as the target for all methods call that needs it
     */
    public function __construct(YamlObject $obj)
    {
        $this->_obj = $obj;
    }

    /**
     * Adds a reference.
     *
     * @param string $name  The reference name
     * @param mixed  $value The reference value
     *
     * @throws \UnexpectedValueException  (description)
     * @return null
     */
    public function addReference(string $name, $value)
    {
        if (empty($name)) {
            throw new \UnexpectedValueException(self::UNAMED_REFERENCE);
        }
        $this->_references[$name] = $value;
    }

    /**
     * Return the reference saved by $name
     *
     * @param string $name Name of the reference
     *
     * @return mixed Value of the reference
     * @throws \UnexpectedValueException    if there's no reference by that $name
     */
    public function &getReference($name)
    {
        if (array_key_exists($name, $this->_references)) {
            return $this->_references[$name];
        }
        throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE, $name));
    }

    /**
     * Return array with all references as Keys and their values, declared for this YamlObject
     *
     * @return array
     */
    public function getAllReferences():array
    {
        return $this->_references;
    }

    /**
     * Adds a comment.
     *
     * @param int    $lineNumber The line number at which thecomment should appear
     * @param string $value      The comment
     *
     * @return null
     */
    public function addComment(int $lineNumber, $value)
    {
        $this->_comments[$lineNumber] = $value;
    }

    /**
     * Gets the comment at $lineNumber
     *
     * @param int|null $lineNumber The line number
     *
     * @return string|array The comment at $lineNumber OR all comments.
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
     * @param string $value The value
     *
     * @return YamlObject
     */
    public function setText(string $value):YamlObject
    {
        $this->value .= ltrim($value);//throw new \Exception(__METHOD__, 1);
        return $this->_obj;
    }

    /**
     * TODO:  what to do with these tags ???
     * Adds a tag.
     *
     * @param string $value The value
     *
     * @return null
     */
    public function addTag(string $value)
    {
        $this->_tags[] = $value;
    }

    /**
     * Determines if it has YAML document start string => '---'.
     *
     * @return boolean  True if has document start, False otherwise.
     */
    public function hasDocStart()
    {
        return is_bool($this->_hasDocStart);
    }

    /**
     * Sets the document start.
     *
     * @param null|bool $value The value : null = no docstart, true = docstart before document comments, false = docstart after document comments
     *
     * @return null
     */
    public function setDocStart($value)
    {
        $this->_hasDocStart = $value;
    }

    /**
     * Is the whole YAML document (YamlObject) tagged ?
     *
     * @return bool
     */
    public function isTagged()
    {
        return !empty($this->_tags);
    }

}
