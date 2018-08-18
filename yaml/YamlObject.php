<?php
namespace Dallgoot\Yaml;

/**
 *  The returned object representing a YAML file content
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 *
 * @method void addReference(string $name, $value)
 * @method mixed getReference(string $name)
 * @method array getAllReferences()
 * @method void addComment($index, $value)
 * @method string|array getComment($lineNumber)
 * @method void setText(string $value)
 * @method void addTag(string $value)
 */
class YamlObject extends \ArrayIterator implements \JsonSerializable
{
    private $__yaml__object__api;

    private const UNDEFINED_METHOD = self::class.": undefined method '%s' ! valid methods are %s";

    public function __construct()
    {
        parent::__construct([], 1); //1 = Array indices can be accessed as properties in read/write.
        $this->__yaml__object__api = new API();
    }

    //TODO: determine accessible methods : private OR public
    public function __call($funcName, $arguments)
    {
        $reflectAPI = new \ReflectionClass(get_class($this->__yaml__object__api));
        $getName = function ($o) { return $o->name; };
        $publicApi  = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PUBLIC));
        $privateApi = array_map($getName, $reflectAPI->getMethods(\ReflectionMethod::IS_PRIVATE));
        if (!in_array($funcName, $publicApi) && !in_array($funcName, $privateApi)) {
            throw new \BadMethodCallException(sprintf(self::UNDEFINED_METHOD, $funcName, implode(",", $publicApi)), 1);
        }
        return call_user_func_array([$this->__yaml__object__api, $funcName], $arguments);
    }

    public function __toString():string
    {
        return $this->__yaml__object__api->value ?? serialize($this);
    }

    public function jsonSerialize()
    {
        $prop = get_object_vars($this);
        unset($prop["__yaml__object__api"]);
        if (count($prop) > 0) return $prop;
        if (count($this) > 0) return iterator_to_array($this);
        return $this->__yaml__object__api->value;
    }
}
