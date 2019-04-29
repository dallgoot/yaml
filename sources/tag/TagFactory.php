<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes\NodeGeneric;

/**
 * Provides mechanisms to handle tags
 * - registering tags and their handler methods
 * - returning transformed values according to Node type or NodeList
 *
 * Note: currently supports ONLY local tags
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 *
 * @todo move legacy tags handlers in a specific class : checking affecting methods to tags mechanisms when theres a global tag
 */
class TagFactory
{
    private const UNKNOWN_TAG = 'Error: tag "%s" is unknown (have you registered a handler for it? see TagFactory)';
    private const NO_NAME     = '%s Error: a tag MUST have a name';
    private const WRONG_VALUE = "Error : cannot transform tag '%s' for type '%s'";

    public static $tagsNamespaces = [];
/**
 * The primary tag handle is a single “!” character.
 * # Global
%TAG ! tag:example.com,2000:app/
---
!foo "bar"
 The secondary tag handle is written as “!!”. This allows using a compact notation for a single “secondary” name space. By default, the prefix associated with this handle is “tag:yaml.org,2002:”. This prefix is used by the YAML tag repository.
 %TAG !! tag:example.com,2000:app/
---
!!int 1 - 3 # Interval, not integer


Named Handles

    A named tag handle surrounds a non-empty name with “!” characters. A handle name must not be used in a tag shorthand unless an explicit “TAG” directive has associated some prefix with it.

    The name of the handle is a presentation detail and must not be used to convey content information. In particular, the YAML processor need not preserve the handle name once parsing is completed.

%TAG !e! tag:example.com,2000:app/
---
!e!foo "bar"
 */
    /**
     * Add Handlers for legacy Yaml tags
     *
     * @see self::LEGACY_TAGS_HANDLERS
     * @todo remove dependency to ReflectionClass using 'get_class_methods'
     */
    private static function registerLegacyNamespace()
    {
        // $reflectAPI = new \ReflectionClass(self::class);
        // $methodsList = [];
        // $list = $reflectAPI->getMethods(RM::IS_FINAL | RM::IS_STATIC & RM::IS_PRIVATE);
        // foreach ($list as $method) {
        //     $methodsList[$method->name] = $method->getClosure();
        // }
        // foreach (self::LEGACY_TAGS_HANDLERS as $tagName => $methodName) {
        //     self::$tagsNamespaces[$tagName] = $methodsList[$methodName];
        // }
    }

    /**
     * transform a Node type based on the tag ($identifier) provided
     *
     * @param      string      $identifier  The identifier
     * @param      mixed      $value       The value
     *
     * @throws     \Exception  Raised if the Tag $identifier is unknown (= not in TagDefault nor registered by user)
     *
     * @return     mixed      If $value can be preserved as Node type :the same Node type,
     *                        otherwise any type that the tag transformation returns
     */
    public static function transform(string $identifier, $value)
    {
        if (self::isKnown($identifier)) {
            if (!($value instanceof NodeGeneric) && !($value instanceof NodeList) ) {
                throw new \Exception(sprintf(self::WRONG_VALUE, $identifier, gettype($value)));
            }
            // return self::$tagsNamespaces[$identifier]($value);
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
        if (count(self::$tagsNamespaces) === 0) {
            self::registerLegacyNamespace();
        }
        return in_array($identifier, array_keys(self::$tagsNamespaces));
    }

    /**
     * Allow the user to add a custom tag handler.
     * Note: That allows to replace handlers for legacy tags also.
     *
     * @param      string      $name   The name
     * @param      Closure     $func   The function
     *
     * @throws     \Exception  Can NOT add handler without a name for the tag
     */
    public static function addTagHandler(string $name, \Closure $func)
    {
        if (empty(trim($name))) {
            throw new \Exception(sprintf(self::NO_NAME, __METHOD__));
        }
        self::$tagsNamespaces[trim($name)] = $func;
    }

}
