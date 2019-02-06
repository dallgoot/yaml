<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 * @todo    handle tags like  <tag:clarkevans.com,2002:invoice>
 */
class NodeTag extends NodeActions
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
    }

    /**
     * For certain (special) Nodes types some actions are required BEFORE parent assignment
     *
     * @param Node   $previous   The previous Node
     * @param array  $emptyLines The empty lines
     *
     * @return boolean  if True self::parse skips changing previous and adding to parent
     */
    public function specialProcess(Node &$previous, array &$emptyLines):bool
    {
        if (is_null($this->value)) {
            $this->value = '';
        }
        return false;
    }

    public function isAwaitingChildren()
    {
        return is_null($this->value);
    }



    /**
     * Builds a tag and its value (also built) and encapsulates them in a Tag object.
     *
     * @param array|object|null $parent The parent
     *
     * @return Tag|mixed The tag object of class Dallgoot\Yaml\Tag.
     */
    public function build(&$parent = null)
    {
        $value = $this->value;
        if ($value instanceof NodeItem) {
            $mother = new NodeSequence();
            $mother->add($value);
            $value = $mother;
        }
        if ($value instanceof NodeKey) {
            $mother = new NodeMapping();
            $mother->add($value);
            $value = $mother;
        }
        $tag = new Tag($this->identifier, $value);
        //soit le tag est connu et on build sa valeur transformée
        $tag->build($parent);
        return $tag->isKnown() ? $tag->value : $tag;
    }

}