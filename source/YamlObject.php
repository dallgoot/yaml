<?php
namespace Dallgoot\Yaml;

/**
 *  The returned object representing a YAML file content
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 *
 * @method void addReference(string $name, $value)
 * @method mixed getReference(string $name)
 * @method array getAllReferences()
 * @method void addComment($index, $value)
 * @method string|array getComment($lineNumber)
 * @method void setText(string $value)
 * @method void addTag(string $value)
 * @method bool hasDocStart()
 */
class YamlObject extends \ArrayIterator implements \JsonSerializable
{
    /** @var API */
    private $__yaml__object__api;

    private const UNDEFINED_METHOD = self::class.": undefined method '%s', valid methods are %s";

    public function __construct()
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
        $this->__yaml__object__api = new API($this);
    }

    /**
     * Transfer method calls to Yaml::API object
     *
     * @param  string                   $funcName   The function name
     * @param  mixed                    $arguments  The arguments
     *
     * @throws \BadMethodCallException  if method isn't part of the public API
     *
     * @return mixed                    the return value of the API::method called
     */
    public function __call($funcName, $arguments)
    {
        $reflectAPI = new \ReflectionClass(get_class($this->__yaml__object__api));
        $getName = function ($o) { return $o->name; };
        $publicApi  = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PUBLIC));
        if (!in_array($funcName, $publicApi) ) {
            throw new \BadMethodCallException(sprintf(self::UNDEFINED_METHOD, $funcName, implode(",", $publicApi)), 1);
        }
        return call_user_func_array([$this->__yaml__object__api, $funcName], $arguments);
    }

    /**
     * Returns a string representation of the YamlObject when
     * it has NO property NOR keys ie. is only a LITTERAL
     *
     * @return     string  String representation of the object.
     */
    public function __toString():string
    {
        return $this->__yaml__object__api->value ?? serialize($this);
    }

    /**
     * Filters unwanted property for JSON serialization
     *
     * @return   mixed  Array (of object properties or keys) OR string if LITTERAL only
     */
    public function jsonSerialize()
    {
        $prop = get_object_vars($this);
        unset($prop["__yaml__object__api"]);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
        return $this->__yaml__object__api->value ?? "Empty YamlObject";
    }
}
