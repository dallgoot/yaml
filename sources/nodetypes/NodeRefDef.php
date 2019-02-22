<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeRefDef extends NodeActions
{
    // public function __construct(string $nodeString, int $line)
    // {
    //     parent::__construct($nodeString, $line);
    // }

    public function isAwaitingChildren():bool
    {
        return is_null($this->value);
    }


}