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
class TagFactory
{
    private const UNKNOWN_TAG = 'Error: tag "%s" is unknown (have you registered an handler for it? see TagFactory)';
    private const NO_NAME = '%s Error: a tag MUST have a name';
    private const WRONG_VALUE = "Error : cannot transform tag '%s' for type '%s'";
    private const LEGACY_TAGS_HANDLERS = ['!!str'       => 'strHandler',
                                          '!!binary'    => 'binaryHandler',
                                          '!set'        => 'setHandler',
                                          '!!omap'       => 'omapHandler',
                                          '!php/object' => 'symfonyPHPobjectHandler',
                                          '!inline'     => 'inlineHandler',
                                      ];

    public static $registeredHandlers = [];

    /**
     * Add Handlers for legacy Yaml tags
     *
     * @see self::LEGACY_TAGS_HANDLERS
     */
    private static function registerLegacyTags()
    {
        $reflectAPI = new \ReflectionClass(self::class);
        $methodsList = [];
        $list = $reflectAPI->getMethods(RM::IS_FINAL | RM::IS_STATIC & RM::IS_PRIVATE);
        foreach ($list as $method) {
            $methodsList[$method->name] = $method->getClosure();
        }
        foreach (self::LEGACY_TAGS_HANDLERS as $tagName => $methodName) {
            self::$registeredHandlers[$tagName] = $methodsList[$methodName];
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
            $phpObject = unserialize($node->raw);
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
            // if ($node instanceof NodeKey) {
            //     return $node;
            // }
            $value = trim($node->raw);
            if ($node instanceof NodeQuoted) {
                $value = $node->build();
            }
            // return new NodeQuoted("'".$value.'"', $node->line);
            return $value;
        } elseif ($node instanceof NodeList) {
            $list = [];
            foreach ($node as $key => $child) {
                // $list[] = self::strHandler($child)->raw;
                $list[] = self::strHandler($child);
            }
            return new NodeScalar(implode('',$list), 0);
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
    private final static function binaryHandler($node, Node &$parent = null)
    {
        return self::strHandler($node, $parent);
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
            ///no actions needed for now
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
    private final static function omapHandler(object $node, Node &$parent = null)
    {
        if ($node instanceof Node) {
            if ($node instanceof NodeItem) {
                return self::omapHandler($node->value);
            } elseif ($node instanceof NodeKey) {
                return $node;
            } else {
                throw new \Exception("tag '!!omap' MUST have items _with_ a key");
            }
        } elseif ($node instanceof NodeList) {
            //verify that each child is an item with a key as child
            $list = new NodeList();
            foreach ($node as $key => $item) {
                $list->push(self::omaphandler($item));
            }
            return $list;
        }
    }

    public static function transform(string $identifier, $value)
    {
        if (self::isKnown($identifier)) {
            if (!($value instanceof Node) && !($value instanceof NodeList) ) {
                throw new \Exception(sprintf(self::WRONG_VALUE, $identifier, gettype($value)));
            }
            return self::$registeredHandlers[$identifier]($value);
        } else {
            throw new \Exception(sprintf(self::UNKNOWN_TAG, $identifier), 1);
        }
    }

    /**
     * Determines if current is known : either YAML legacy or user added
     *
     * @return     boolean  True if known, False otherwise.
     */
    public static function isKnown(string $identifier):bool
    {
        if (count(self::$registeredHandlers) === 0) {
            self::registerLegacyTags();
        }
        return in_array($identifier, array_keys(self::$registeredHandlers));
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

         regex (([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
        */
    // }
}
