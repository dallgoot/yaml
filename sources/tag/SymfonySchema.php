<?php
namespace Dallgoot\Yaml\Tag;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes;

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
     * @param object             $node   The node
     * @param object|array|null  $parent The parent
     *
     * @throws Exception if unserialize fails OR if its a NodeList (no support of multiple values for this tag)
     * @return object    the unserialized object according to Node value
     */
    public final static function PHPobjectHandler(object $node)
    {
        if ($node instanceof Nodes\Scalar) {
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

    public function __call($name, $arguments) {
        //TODO : handle 'php/object'
        throw new \Exception("no handler for tag '$name' in ".self::class, 1);
    }
}
