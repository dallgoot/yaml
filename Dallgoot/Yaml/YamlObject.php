<?php
namespace Dallgoot\Yaml;
/**
 * 
 */
class YamlObject extends \ArrayObject
{
    private $__yaml__object__api;
    function __construct($propertiesHolder)
    {
        $this->$__yaml__object__api = new API();
    }
    public function __call($name, $arguments)
    {
        $reflectAPI = new \ReflectionClass(get_class($this->$__yaml__object__api));
        $getName = function($o){ return $o->name; };
        $api = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PUBLIC));
        if (!array_key_exists($name, $api)) {
            throw new \BadMethodCallException("undefined method '$name' ! valid methods are ".(implode(",",$api)), 1);
        }
        return call_user_func_array([$this->__yaml__object__api, $name], $arguments);
    }
}