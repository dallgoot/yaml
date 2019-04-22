<?php
namespace Dallgoot\Yaml;

use \ReflectionMethod as RM;
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
class TagNamespaceLocal implements TagNamespaceInterface
{
    public const LEGACY_TAGS_HANDLERS = ['!!str'       => 'strHandler',
                                          '!!binary'    => 'binaryHandler',
                                          '!set'        => 'setHandler',
                                          '!!omap'      => 'omapHandler',
                                          '!php/object' => 'symfonyPHPobjectHandler',
                                          '!inline'     => 'inlineHandler',
                                      ];


    /**
     * Specific Handler for Symfony custom tag : 'php/object'
     *
     * @param object             $node   The node
     * @param object|array|null  $parent The parent
     *
     * @throws Exception if unserialize fails OR if its a NodeList (no support of multiple values for this tag)
     * @return object    the unserialized object according to Node value
     */
    public final static function symfonyPHPobjectHandler(object $node, &$parent = null)
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
    public final static function inlineHandler(object $node, object &$parent = null)
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
    public final static function strHandler(object $node, object &$parent = null)
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
    public final static function binaryHandler($node, Node &$parent = null)
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
    public final static function setHandler(object $node, Node &$parent = null)
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
    public final static function omapHandler(object $node, Node &$parent = null)
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

}
