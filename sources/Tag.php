<?php
namespace Dallgoot\Yaml;

use \ReflectionMethod as RM;
/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Tag
{
    /** @var string */
    public $tagName;
    /** @var Node|null|string */
    public $value;
    /** @var null|Node|NodeList */
    private $raw;

    private const NO_NAME = '%s Error: a tag MUST have a name';
    private const WRONG_VALUE = "Error : cannot transform tag '%s' for type '%s'";
    private const LEGACY_TAGS_HANDLERS = ['!str'       => 'strHandler',
                                          '!binary'    => 'binaryHandler',
                                          '!set'       => 'setHandler',
                                          '!omap'      => 'mapHandler',
                                          'php/object' => 'symfonyPHPobjectHandler',
                                          '!inline'    => 'inlineHandler',
                                          '!long'      => 'longHandler'];

    public static $registeredHandlers = [];

    /**
     * Tag constructor.
     *
     * @param string $tagName the name of the tag like '!!str' (WITHOUT the first "!")
     * @param mixed  $raw     any PHP variable type
     *
     * @throws \Exception if $tagName is an invalid string or absent
     */
    public function __construct(string $tagName, $raw)
    {
        if (empty($tagName)) {
            throw new \Exception(sprintf(self::NO_NAME, __METHOD__));
        }
        $this->tagName = $tagName;
        $this->raw = $raw;
        if (empty(self::$registeredHandlers)) $this->registerLegacyTags();
    }

    /**
     * Add Handlers for legacy Yaml tags
     *
     * @see self::LEGACY_TAGS_HANDLERS
     */
    private function registerLegacyTags()
    {
        $reflectAPI = new \ReflectionClass(self::class);
        $methodsList = [];
        $list = $reflectAPI->getMethods(RM::IS_FINAL | RM::IS_STATIC & RM::IS_PRIVATE);
        foreach ($list as $method) {
            $methodsList[$method->name] = $method->getClosure($this);
        }
        foreach (self::LEGACY_TAGS_HANDLERS as $tagName => $methodName) {
            self::$registeredHandlers[$tagName] = $methodsList[$methodName];
        }
    }

    /**
     * Checked that handler argument is either Node or NodeList
     *
     * @param string $tagName  The tag name
     * @param $node  $node     The candidate Node|NodeList which will be processed by handler
     *
     * @throws \Exception  if $node is not Node or NodeList
     * @return null
     */
    private function checkHandlerArgument($tagName, $node)
    {
        if (!($node instanceof Node) && !($node instanceof NodeList) ) {
            throw new \Exception(sprintf(self::WRONG_VALUE, $tagName, gettype($node)));
        }
    }

    /**
     * Specific Handler for Symfony custom tag : 'php/object'
     *
     * @param object             $node   The node
     * @param object|array|null  $parent The parent
     *
     * @throws Exception if unserialize fails OR if its a NodeList (no support of multiple values for this tag)
     * @return object    the unserialized object according to Node value
     */
    private final static function symfonyPHPobjectHandler(object $node, &$parent = null)
    {
        if ($node instanceof NodeScalar) {
            $phpObject = unserialize($node->value);
            // NOTE : we assume this is only used for Object types (if a boolean false is serialized this will FAIL)
            if (is_bool($phpObject)) {
                throw new \Exception("value for tag 'php/object' could NOT be unserialized");
            }
            return $phpObject;
        } elseif ($node instanceof NodeList) {
            throw new \Exception("tag 'php/object' can NOT be a NodeList");
        }
    }
    /**
     * Specific handler for 'inline' tag
     *
     * @param object $node
     * @param object|array|null  $parent The parent
     *
     * @todo implements
     */
    private final static function inlineHandler(object $node, object &$parent = null)
    {
        return self::strHandler($node, $parent);
    }


    /**
     * Specific handler for 'long' tag
     *
     * @param object $node
     * @param object|array|null  $parent The parent
     *
     * @todo implements
     */
    private final static function longHandler(object $node, object &$parent = null)
    {
        return self::strHandler($node, $parent);
    }


    /**
     * Specific Handler for 'str' tag
     *
     * @param object $node    The Node or NodeList
     * @param object|array|null  $parent The parent
     *
     * @return string the value of Node converted to string if needed
     */
    private final static function strHandler(object $node, object &$parent = null)
    {
        if ($node instanceof Node) {
            if ($node instanceof NodeKey) $node->build($parent);
            return ltrim($node->raw);
        // } elseif ($node instanceof NodeList) {
        //     return Builder::buildLitteral($node);
        }
    }

    /**
     * Specific Handler for 'binary' tag
     *
     * @param object $node   The node or NodeList
     * @param object|array|null  $parent The parent
     *
     * @return string  The value considered as 'binary' Note: the difference with strHandler is that multiline have not separation
     */
    private final static function binaryHandler(object $node, Node &$parent = null)
    {
        if ($node instanceof Node) {
            $out = '';
            $cursor = $node->value;
            while ($cursor instanceof Node) {
                $out .= $cursor->value;
                $cursor = $cursor->value;
            }
            return ltrim($out);
        // } elseif ($node instanceof NodeList) {
        //     return Builder::buildLitteral($node, Y::RAW);
        }
    }

    /**
     * Specific Handler for the '!set' tag
     *
     * @param      object     $node    The node
     * @param object|array|null  $parent The parent
     *
     * @throws     \Exception  if theres a set but no children (set keys or set values)
     * @return     YamlObject|object  process the Set, ie. an object construction with properties as serialized JSON values
     */
    private final static function setHandler(object $node, Node &$parent = null)
    {
        if (!($node instanceof NodeList)) {
            throw new \Exception("tag '!!set' can NOT be a single Node");
        } else {
            // if ($parent instanceof YamlObject) {
            //     Builder::buildNodeList($node, $parent);
            // } else {
            //     return Builder::buildNodeList($node, $parent);
            // }
        }
    }

    /**
     * Specifi Handler for the 'omap' tag
     *
     * @param object $node   The node
     * @param object|array|null  $parent The parent
     *
     * @throws \Exception  if theres an omap but no map items
     * @return YamlObject|array process the omap
     */
    private final static function mapHandler(object $node, Node &$parent = null)
    {
        if (!($node instanceof NodeList)) {
            throw new \Exception("tag '!!omap' can NOT be a single Node");
        } else {
            // $node->type = Y::SEQUENCE;
            // if ($parent instanceof YamlObject) {
            //     Builder::buildNodeList($node, $parent);
            // } else {
            //     return Builder::buildNodeList($node, $parent);
            // }
        }
    }

    /**
     * Return the tagged value according to Tag type
     *
     * @param mixed $parent The parent (object|array) IF any.
     *
     * @return mixed
     */
    public function build(&$parent = null)
    {
        if (is_scalar($this->raw) || is_null($this->raw)) {
            $this->value = $this->raw;
        } elseif ($this->isKnown()) {
            $this->checkHandlerArgument($this->tagName, $this->raw);
            $this->value = self::$registeredHandlers[$this->tagName]($this->raw, $parent);
        } elseif ($this->value instanceof NodeKey) {
            $this->value->build($parent);
        } elseif ($this->value instanceof NodeItem) {
            $this->value->build($parent);
        } else {
            $this->value->build($this->raw, $parent);
        }
    }

    /**
     * Determines if current is known : either YAML legacy or user added
     *
     * @return     boolean  True if known, False otherwise.
     */
    public function isKnown():bool
    {
        return in_array($this->tagName, array_keys(self::$registeredHandlers));
    }

    /**
     * Allow the user to add a custome tag handler.
     * Note: That allows to replace handlers for legacy tags also.
     *
     * @param      string      $name   The name
     * @param      Closure     $func   The function
     *
     * @throws     \Exception  Can NOT add handler without a name for the tag
     */
    public static function addTagHandler(string $name, \Closure $func)
    {
        if (empty($name)) {
            throw new \Exception(sprintf(self::NO_NAME, __METHOD__));
        }
        self::$registeredHandlers[$name] = $func;
    }



    /**
     * Should verify if the tag is correct
     *
     * @param string $providedName The provided name
     * @todo  is this required ???
     */
    // private function checkNameValidity(string $providedName)
    // {
        /* TODO  implement and throw Exception if invalid (setName method ???)
         *The suffix must not contain any “!” character. This would cause the tag shorthand to be interpreted as having a named tag handle. In addition, the suffix must not contain the “[”, “]”, “{”, “}” and “,” characters. These characters would cause ambiguity with flow collection structures. If the suffix needs to specify any of the above restricted characters, they must be escaped using the “%” character. This behavior is consistent with the URI character escaping rules (specifically, section 2.3 of RFC2396).
        */
    // }
}
