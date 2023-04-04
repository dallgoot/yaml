<?php
namespace Dallgoot\Yaml\Tag;

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
 */
interface SchemaInterface
{
    /** @var string */
    // const SCHEMA_URI = null;
    // /** @var string */
    // const BUILDING_NAMESPACE = null;

    /**
     * When the tag is not a method on the SchemaClass provide the logic to handle it
     */
    public function __call($name, $arguments);
}