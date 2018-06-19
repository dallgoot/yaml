<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\API as API;

/**
 * 
 */
class YamlObject extends \ArrayIterator
{
    private $__yaml__object__api;
    public $value;
    private $_locked;
    
    public function __construct()
    {
        $this->__yaml__object__api = new API();
    }
    
    public function __call($funcName, $arguments)
    {
        $reflectAPI = new \ReflectionClass(get_class($this->__yaml__object__api));
        $getName = function ($o) { return $o->name; };
        $publicApi  = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PUBLIC));
        $privateApi = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PRIVATE));
        if (!in_array($funcName, $publicApi) && 
        	(!in_array($funcName, $privateApi) || $this->_locked)) {
	            throw new \BadMethodCallException("undefined method '$funcName' ! valid methods are ".(implode(",", $publicApi)), 1);
        	
        }
        return call_user_func_array([$this->__yaml__object__api, $funcName], $arguments);
    }

    public function __toString()
    {
        if (!is_string($this->value)) {
            return serialize($this);            
        }
        return $this->value;
    }

    public function lock()
    {
    	$this->_locked = true;
    }
}