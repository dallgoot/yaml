<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeAnchor extends NodeActions
{
    public function __construct(string $identifier, int $line)
    {
        $this->identifier = $identifier;
    }

    public function build(&$parent = null)
    {
        return $this->getRoot()->getYamlObject()->getReference(substr($this->identifier, 1));
    }

    public function isAwaitingChildren():bool
    {
        return false;
    }
}