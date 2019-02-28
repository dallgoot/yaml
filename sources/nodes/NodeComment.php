<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeComment extends Node
{
   public function specialProcess(Node &$previous, array &$emptyLines):bool
   {
        $previous->getRoot()->add($this);
        return true;
   }

   public function build(&$parent = null)
   {
        $root = $this->getRoot();
        $yamlObject = $root->getYamlObject();
        $yamlObject->addComment($this->line, $this->raw);
   }
}