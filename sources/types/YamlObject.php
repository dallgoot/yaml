<?php
namespace Dallgoot\Yaml;

/**
 *  The returned object representing a YAML document
 *  Methods are provided by encapsulated Yaml\API object.
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 *
 * @method void addReference(string $name, $value)
 * @method mixed getReference(string $name)
 * @method array getAllReferences()
 * @method void addComment($index, $value)
 * @method string|array getComment($lineNumber)
 * @method void setText(string $value)
 * @method void addTag(string $handle, string $prefix)
 * @method bool hasDocStart()
 */
class YamlObject extends \ArrayIterator implements \JsonSerializable
{
    /** @var API */
    private $__yaml__object__api;

    private const UNDEFINED_METHOD = self::class.": undefined method '%s', valid methods are (%s)";

    /**
     * Construct the YamlObject making sure the indices can be accessed directly
     * and creates the API object with a reference to this YamlObject.
     * @todo check indices access outside of foreach loop
     */
    public function __construct()
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
        $this->__yaml__object__api = new API($this);
    }

    /**
     * Transfer method calls to Yaml::API object
     *
     * @param string $funcName  The function name
     * @param mixed  $arguments The arguments
     *
     * @throws \BadMethodCallException if method isn't part of the public API
     * @return mixed                    the return value of the API::method called
     * @todo remove dependency to ReflectionClass calling $funcName directly in a try/catch block
     */
    public function __call($funcName, $arguments)
    {
        $reflectAPI = new \ReflectionClass(get_class($this->__yaml__object__api));
        $getName    = function ($o) { return $o->name; };
        $publicApi  = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PUBLIC));
        if (!in_array($funcName, $publicApi) ) {
            throw new \BadMethodCallException(sprintf(self::UNDEFINED_METHOD, $funcName, implode(",", $publicApi)));
        }
        return call_user_func_array([$this->__yaml__object__api, $funcName], $arguments);
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
