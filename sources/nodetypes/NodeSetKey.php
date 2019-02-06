<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeSetKey extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        $v = trim(substr($nodeString, 1));
        if (!empty($v)) {
            // $node->value = new NodeList(new Node($v, $node->line));
            $this->value = NodeFactory::get($v, $line);
        }
    }

    /**
     * Builds a set key.
     *
     * @param object $parent The parent
     *
     * @throws \Exception  if a problem occurs during serialisation (json format) of the key
     */
    public function build(&$parent = null)
    {
        $built = is_object($this->value) ? $this->value->build($parent) : null;
        $stringKey = is_string($built) && Regex::isProperlyQuoted($built) ? trim($built, '\'" '): $built;
        $key = json_encode($stringKey, JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if (empty($key)) throw new \Exception("Cant serialize complex key: ".var_export($this->value, true));
        $parent->{trim($key, '\'" ')} = null;
    }

}