<?php
namespace Dallgoot\Yaml;

/**
 * the return Object representing a YAML file content
 */
class Yaml_API
{
    private $_references = [];
    private $_comments   = [];
    private $_documents  = [];

    private  $_type = NT::MAPPING;
    /*
     *consider dumping datetime as date strings according to a format provided by user or default
    */
    public function __construct($objectTemplate)
    {
        // $this->_references = $objectTemplate->_ 
    }

    public function getReference($referenceName = null)
    {
        if (array_key_exists($referenceName, $this->_references)) {
            return $this->_references[$referenceName];
        }
        return $this->_references; 
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
}
