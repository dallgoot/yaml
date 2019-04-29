<?php

namespace Dallgoot\Yaml\Nodes;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Comment extends NodeGeneric
{
   public function specialProcess(NodeGeneric &$previous, array &$emptyLines):bool
   {
        $previous->getRoot()->add($this);
        return true;
   }

   public function build(&$parent = null)
   {
        $root = $this->getRoot();
        $yamlObject = $root->getYamlObject();
        $yamlObject->addComment($this->line, $this->raw);
        return null;
   }
}