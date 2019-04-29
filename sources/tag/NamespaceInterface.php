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
 * @todo move legacy tags handlers in a specific class : checking affecting methods to tags mechanisms when theres a global tag
 */
interface NamespaceInterface
{
    /** @var string */
    const NS_ROOT_NAME = 'tag:yaml.org,2002:';

}