<?php

namespace Dallgoot\Yaml\Tag;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Scalar;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class SymfonySchema implements SchemaInterface
{
    const SCHEMA_URI = 'tag:symfony.com,2019:';
    const BUILDING_NAMESPACE = "\\Symfony\\Component";

    /**
     *
     * Specific Handler for Symfony custom tag : 'php/object'
     *
     *
     * @throws \Exception if unserialize fails OR if its a NodeList (no support of multiple values for this tag)
     */
    public final static function PHPobjectHandler(object $node): object
    {
        if ($node instanceof Scalar) {
            $phpObject = unserialize($node->raw);
            // NOTE : we assume this is only used for Object types (if a boolean false is serialized this will FAIL)
            if (is_bool($phpObject)) {
                throw new \Exception("value for tag 'php/object' could NOT be unserialized");
            }
            return $phpObject;
        }
        throw new \Exception("tag 'php/object' value must NOT be a list");
    }

    public function __call($name, $arguments)
    {
        //TODO : handle 'php/object'
        if(in_array($name, ['php/object'])) {
            return match($name) {
                'php/object' => self::PHPobjectHandler($arguments),
                default => null,
            };
        }
        throw new \Exception("no handler for tag '$name' in " . self::class, 1);
    }
}
