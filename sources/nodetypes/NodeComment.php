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
     * According to the current Node type and deepest value
     * this indicates if self::parse skips (or not) the parent and previous assignment
     *
     * @param      Node     $target    The parent target Node
     *
     * @return     boolean  True if context, False otherwiser
     * @todo  is this really necessary according ot other checkings out there ?
     */
    public function skipOnContext(Node &$target):bool
    {
        if (!$this->identifier) {
            $target = $target->getParent(-1);//if alone-on-line comment --> set parent to root
            return true;
        }
        return false;
    }

   public function needsSpecialProcess(Node &$previous, array &$emptyLines):bool
   {
        $deepest = $previous->getDeepestNode();
        if (!($previous->getParent() instanceof NodeLiterals)
            && !($deepest instanceof NodeLiterals)) {
            $previous->getParent(-1)->add($this);
            return true;
        }
        return false;
   }
}