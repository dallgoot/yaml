<?php
namespace Dallgoot\Yaml;

/**
 *  The returned object representing a YAML document
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class YamlObject extends \ArrayIterator implements \JsonSerializable
{
    /** @var YamlProperties */
    private $__yaml__object__api;

    const UNDEFINED_METHOD = self::class.": undefined method '%s', valid methods are (addReference,getReference,getAllReferences,addComment,getComment,setText,addTag,hasDocStart,isTagged)";
    const UNKNOWN_REFERENCE = "no reference named: '%s', known are : (%s)";
    const UNAMED_REFERENCE  = "reference MUST have a name";
    const TAGHANDLE_DUPLICATE = "Tag handle '%s' already declared before, handle must be unique";

    /**
     * Construct the YamlObject making sure the indices can be accessed directly
     * and creates the API object with a reference to this YamlObject.
     * @todo check indices access outside of foreach loop
     */
    public function __construct($buildingOptions)
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
        $this->__yaml__object__api = new YamlProperties($buildingOptions);
    }

    /**
     * Returns a string representation of the YamlObject when
     * it has NO property NOR keys ie. is only a LITTERAL
     *
     * @return string String representation of the object.
     */
    public function __toString():string
    {
        return $this->__yaml__object__api->value ?? serialize($this);
    }

    public function getOptions()
    {
        return $this->__yaml__object__api->_options;
    }
    /**
     * Adds a reference.
     *
     * @param string $name  The reference name
     * @param mixed  $value The reference value
     *
     * @throws \UnexpectedValueException  (description)
     * @return mixed
     */
    public function &addReference(string $name, $value)
    {
        if (empty($name)) {
            throw new \UnexpectedValueException(self::UNAMED_REFERENCE);
        }
        // var_dump("DEBUG: '$name' added as reference");
        $this->__yaml__object__api->_anchors[$name] = $value;
        return $this->__yaml__object__api->_anchors[$name];
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
        if (array_key_exists($name, $this->__yaml__object__api->_anchors)) {
            return $this->__yaml__object__api->_anchors[$name];
        }
        throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE,
                                                    $name, implode(',',array_keys($this->__yaml__object__api->_anchors)))
                                                );
    }

    /**
     * Return array with all references as Keys and their values, declared for this YamlObject
     *
     * @return array
     */
    public function getAllReferences():array
    {
        return $this->__yaml__object__api->_anchors;
    }

    /**
     * Adds a comment.
     *
     * @param int    $lineNumber The line number at which the comment should appear
     * @param string $value      The comment
     *
     * @return null
     */
    public function addComment(int $lineNumber, string $value)
    {
        $this->__yaml__object__api->_comments[$lineNumber] = $value;
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
        if (array_key_exists((int) $lineNumber, $this->__yaml__object__api->_comments)) {
            return $this->__yaml__object__api->_comments[$lineNumber];
        }
        return $this->__yaml__object__api->_comments;
    }

    /**
     * Sets the text when the content is *only* a literal
     *
     * @param string $value The value
     *
     * @return YamlObject
     */
    public function setText(string $value):YamlObject
    {
        $this->__yaml__object__api->value .= ltrim($value);
        return $this;
    }

    /**
     * TODO:  what to do with these tags ???
     * Adds a tag.
     *
     * @param string $handle The handle declared for the tag
     * @param string $prefix The prefix/namespace/schema that defines the tag
     *
     * @return null
     */
    public function addTag(string $handle, string $prefix)
    {
        //  It is an error to specify more than one “TAG” directive for the same handle in the same document, even if both occurrences give the same prefix.
        if (array_key_exists($handle, $this->__yaml__object__api->_tags)) {
            throw new \Exception(sprintf(self::TAGHANDLE_DUPLICATE, $handle), 1);
        }
        $this->__yaml__object__api->_tags[$handle] = $prefix;
    }

    /**
     * Determines if it has YAML document start string => '---'.
     *
     * @return boolean  True if document has start, False otherwise.
     */
    public function hasDocStart():bool
    {
        return is_bool($this->__yaml__object__api->_hasDocStart);
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
        $this->__yaml__object__api->_hasDocStart = $value;
    }

    /**
     * Is the whole YAML document (YamlObject) tagged ?
     *
     * @return bool
     */
    public function isTagged()
    {
        return !empty($this->__yaml__object__api->_tags);
    }

    /**
     * Filters unwanted property for JSON serialization
     *
     * @return mixed Array (of object properties or keys) OR string if YAML object only contains LITTERAL (in self::value)
     */
    public function jsonSerialize()
    {
        $prop = get_object_vars($this);
        unset($prop["__yaml__object__api"]);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
        return $this->__yaml__object__api->value ?? "_Empty YamlObject_";
    }
}
