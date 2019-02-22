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
    /**
    * @param string  $nodeString  The node string
    * @param int     $line        The node line
    */
    // public function __construct(string $nodeString, int $line)
    // {
    //     parent::__construct($nodeString, $line);
    //     $this->value = NodeFactory$nodeString;
    // }

   public function specialProcess(Node &$previous, array &$emptyLines):bool
   {
        $previous->getRoot()->add($this);
        return true;
   }

   public function getTargetOnLessIndent(Node &$previous):Node
   {
       return $previous->getRoot();
   }

   public function getTargetOnEqualIndent(Node &$previous):Node
   {
       return $previous->getRoot();
   }

   public function getTargetOnMoreIndent(Node &$previous):Node
   {
       return $previous->getRoot();
   }

   public function build(&$parent = null)
   {
      $target = $parent;
      if ($target instanceof YamlObject) {
         $target->addComment($this->line, $this->raw);
      } else {
        $root = $this->getRoot();
        $yamlObject = $root->getYamlObject();
        $yamlObject->addComment($this->line, $this->raw);
      }
   }
}