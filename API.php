<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Types as T;

/**
 * the return Object representing a YAML file content
 */
class API
{
    private $_references = [];
    private $_comments   = [];
    private $_documents  = [];

    public  $type = T::MAPPING;

    const UNKNOWN_REFERENCE = self::class.": no reference named '%s'";

    /*
     *consider dumping datetime as date strings according to a format provided by user or default
    */
    public function __construct()
    {
        // $this->_references = $objectTemplate->_ 
    }

    /**
     * Adds a reference.  //TODO : what use for $line ???
     *
     * @param      <int>                     $line   The line
     * @param      <string>                     $name   The name
     * @param      <string>                     $value  The value
     *
     * @throws     \UnexpectedValueException  (description)
     */
    public function addReference($line, $name, $value)
    {
        if (is_null($name)) {
            throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE, $referenceName), 1);
        }
        $this->_references[$name] = $value;
    }

    public function &getReference($referenceName)
    {
        if (array_key_exists($referenceName, $this->_references)) {
            return $this->_references[$referenceName];
        }
        throw new \UnexpectedValueException(sprintf(self::UNKNOWN_REFERENCE, $referenceName), 1);
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

    public function setText($string)
    {
        $this->value = $string;
    }
}
