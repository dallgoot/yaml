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
        $trimmed = ltrim($nodeString);
        $pos = strpos($trimmed, ' ');
        if (is_int($pos)) {
            $this->identifier = substr($trimmed, 0, $pos);
            $rest = substr($trimmed, $pos);
            if (!empty($rest)) {
                $n = NodeFactory::get($rest, $line);
                // $n->indent = $this->indent + 4;
                $this->add($n);
            }
        }
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

    public function isAwaitingChildren():bool
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
        if (is_null($parent) && ($value instanceof NodeItem || $value instanceof NodeKey)) {
            $value = new NodeList($this->value);
        }
        //soit le tag est connu et on build sa valeur transformée
        if (TagFactory::isKnown($this->_tag)) {
            TagFactory::transform($this->_tag, $value)->build($parent);
        } else {
            return new Tag($this->_tag, is_null($value) ? null : $value->build($parent));
        }
    }

}