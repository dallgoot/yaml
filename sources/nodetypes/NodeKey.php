<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeKey extends Node
{
    const ERROR_NO_KEYNAME = self::class.": key has NO IDENTIFIER on line %d";

    public function __construct(string $nodeString, int $line, array $matches)
    {
        parent::__construct($nodeString, $line);
        $this->identifier = trim($matches[1], '"\' ');
        $value = isset($matches[2]) ? trim($matches[2]) : null;
        if (!is_null($value)) {
            $hasComment = strpos($value, ' #');
            if (is_bool($hasComment) || Regex::isProperlyQuoted($value)) {
                $child = NodeFactory::get(trim($value), $line);
            } else {
                $child = new NodeComment(trim(substr($value, 0, $hasComment)), $line);
            }
            $this->add($child);
        }
    }

    public function getTargetOnLessIndent(Node $previous):Node
    {
        if ($this->indent === 0) {
            return $previous->getParent(-1);//get root
        } else {
            return parent::getTargetOnLessIndent($previous);
        }
    }

    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous Node
     *
     * @return Node
     */
    public function getTargetonEqualIndent(Node &$previous):Node
    {
        if ($this->indent === 0) {
            return $previous->getParent(-1);//get root
        } else {
            return parent::getTargetonEqualIndent($previous);
        }
    }

    public function isAwaitingChildren()
    {
        return is_null($this->value);
    }



    /**
     * Builds a key and set the property + value to the given parent
     *
     * @param object|array $parent The parent
     *
     * @throws \ParseError if Key has no name(identifier) Note: empty string is allowed
     * @return null
     */
    public function build(&$parent = null)
    {
        if (is_null($this->identifier)) {
            throw new \ParseError(sprintf(self::ERROR_NO_KEYNAME, $this->line));
        } else {
            if (is_null($this->value)) {
                $result = null;
            } elseif ($this->value instanceof Node) {
                $value = $this->value;
                switch (get_class($this->value)) {
                    case 'NodeItem':$mother = new NodeSequence();
                                    $mother->add($this->value);
                                    $value = $mother;
                        break;
                    case 'NodeKey': $mother = new NodeMapping();
                                    $mother->add($this->value);
                                    $value = $mother;
                        break;
                    case 'NodeSetKey':$mother = new NodeSet();
                                    $mother->add($this->value);
                                    $value = $mother;
                        break;
                }
                $result = $value->build($parent);
            } elseif ($this->value instanceof NodeList) {
                $result = Builder::buildNodeList($this->value);
            }
            if (is_null($parent)) {
                return $result;
            } else {
                if (is_array($parent)) {
                    $parent[$this->identifier] = $result;
                } else {
                    $parent->{$this->identifier} = $result;
                }
            }
        }
    }

}