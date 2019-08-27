<?php
namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Tag\CoreSchema;

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
    private const UNKNOWN_TAG = 'Error: tag "%s" is unknown (have you registered a handler for it? see Dallgoot\Yaml\Tag\SchemaInterface)';
    private const NO_NAME     = '%s Error: a tag MUST have a name';
    private const WRONG_VALUE = "Error : cannot transform tag '%s' for type '%s'";
    private const ERROR_HANDLE_EXISTS = "This tag handle is already registered, did you use a named handle like '!name!' ?";

    public static $schemas = [];
    public static $schemaHandles = [];
    /**
 The primary tag handle is a single “!” character.
 # Global
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



Verbatim Tags
    A tag may be written verbatim by surrounding it with the “<” and “>” characters. In this case, the YAML processor must deliver the verbatim tag as-is to the application. In particular, verbatim tags are not subject to tag resolution. A verbatim tag must either begin with a “!” (a local tag) or be a valid URI (a global tag).


  !<!bar> baz


%TAG !e! tag:example.com,2000:app/
---
!e!foo "bar"

%TAG ! tag:example.com,2000:app/
%TAG !! tag:example.com,2000:app/
%TAG !e! tag:example.com,2000:app/
!<tag:yaml.org,2002:str> foo :

     */
    /**
     * Add Handlers for legacy Yaml tags
     *
     * @see self::LEGACY_TAGS_HANDLERS
     * @todo remove dependency to ReflectionClass using 'get_class_methods'
     */
    private static function createCoreSchema()
    {
        $coreSchema = new CoreSchema;
        self::registerSchema($coreSchema::SCHEMA_URI, $coreSchema);
        self::registerHandle("!!", $coreSchema::SCHEMA_URI);
    }

    public static function registerSchema($URI, Tag\SchemaInterface $schemaObject)
    {
        self::$schemas[$URI] = $schemaObject
;    }

    public static function registerHandle(string $handle, string $prefixOrURI)
    {
        if (array_key_exists($handle, self::$schemaHandles)) {
            throw new \Exception(self::ERROR_HANDLE_EXISTS, 1);
        }
        self::$schemaHandles[$handle] = $prefixOrURI;
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
    public static function transform(string $identifier, $value, &$parent = null)
    {
        if (count(self::$schemas) === 0) {
            self::createCoreSchema();
        }
        if (!($value instanceof NodeGeneric) && !($value instanceof NodeList) ) {
              throw new \Exception(sprintf(self::WRONG_VALUE, $identifier, gettype($value)));
        } else {
            // try {
                if (!preg_match(Regex::TAG_PARTS, $identifier, $matches)) {
                    throw new \UnexpectedValueException("Tag '$identifier' is invalid", 1);
                }
                return self::runHandler($matches['handle'],
                                          $matches['tagname'],
                                          $value,
                                          $parent);
            // } catch (\UnexpectedValueException $e) {
            //     return new Tagged($identifier, is_null($value) ? null : $value->build($parent));
            // } catch (\Throwable $e) {
            //     throw new \Exception("Tagged value could not be transformed for tag '$identifier'", 1, $e);;
            // }
        }
    }

    public static function runHandler($handle, $tagname, $value, &$parent = null)
    {
        if (array_key_exists($handle, self::$schemaHandles)) {
            $schemaName = self::$schemaHandles[$handle];
            if (array_key_exists($schemaName, self::$schemas)) {
                $schemaObject = self::$schemas[$schemaName];
                if (is_object($schemaObject) && is_string($tagname)) {
                    return $schemaObject->{$tagname}($value, $parent);
                }
            }
        }
        throw new \UnexpectedValueException("Error Processing tag '$tagname' : in $handle", 1);
    }

}
