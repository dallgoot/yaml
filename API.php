<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

/**
 * the return Object representing a YAML file content
 *  consider dumping datetime as date strings according to a format provided by user or default
 */
class API
{
    private $_references = [];
    private $_comments   = [];
    private $_documents  = [];

    public $type = T::MAPPING;
    public $value = null;

    const UNKNOWN_REFERENCE = self::class.": no reference named '%s'";
    const UNAMED_REFERENCE    = self::class.": reference MUST have a name";

    public function __construct()
    {
    }

    /**
     * Adds a reference.
     *
     * @param      string                    $name   The name
     * @param      mixed                     $value  The value
     *
     * @throws     \UnexpectedValueException  (description)
     */
    public function addReference($name, $value)
    {
        if (is_null($name) || empty($name)) {
            throw new \UnexpectedValueException(self::UNAMED_REFERENCE, 1);
        }
        $this->_references[$name] = $value;
    }

    public function &getReference($name)
    {
        if (array_key_exists($name, $this->_references)) {
            return $this->_references[$name];
        }
        throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE, $name), 1);
    }

    public function getAllReferences()
    {
        return $this->_references;
    }

    public function addComment($index, $value)
    {
        $this->_comments[$index] = $value;
    }

    public function getComment($lineNumber = null)
    {
        if (array_key_exists($lineNumber, $this->_comments)) {
            return $this->_comments[$lineNumber];
        }
        return $this->_comments;
    }

    public function getDocument($identifier = null)
    {
        if (array_key_exists($identifier, $this->_documents)) {
            return $this->_documents[$identifier];
        }
        return count($this->_documents)===1 ? $this->_documents[0] : $this->_documents;
    }

    public function lock()
    {
        $this->locked = true;
    }

    private function setText($value)
    {
        $this->value .= PHP_EOL.$value;
    }
}
