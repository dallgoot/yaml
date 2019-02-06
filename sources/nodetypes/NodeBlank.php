<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeBlank extends Node
{
    public function needsSpecialProcess(Node &$previous, array &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        //what first character to determine if escaped sequence are allowed
        //if this is empty $separator depends on previous last character (escape slash)
        $separator = ' ';
        // if ($deepest->value[-1] !== "\\") {
        //     $deepest->parse(($deepest->value)."\n");
        // } else {
            // $this->specialProcess($previous, $emptyLines);
        // }
               if ($previous instanceof NodeScalar)   $emptyLines[] = $this->setParent($previous->getParent());
        if ($deepest instanceof NodeLiterals) $emptyLines[] = $this->setParent($deepest);
        return true;
    }

    // public function specialProcess(Node &$previous, array &$emptyLines)
    // {
    //     if ($previous instanceof NodeScalar)   $emptyLines[] = $this->setParent($previous->getParent());
    //     if ($deepest instanceof NodeLiterals) $emptyLines[] = $this->setParent($deepest);
    // }
}