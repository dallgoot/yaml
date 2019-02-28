<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeScalar extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $value = trim($nodeString);
        if ($value !== '') {
            $hasComment = strpos($value, ' #');
            if (!is_bool($hasComment)) {
                    $realValue    = trim(substr($value, 0, $hasComment));
                    $commentValue = trim(substr($value, $hasComment));
                    $realNode = NodeFactory::get($realValue, $line);
                    $realNode->indent = null;
                    $commentNode = NodeFactory::get($commentValue, $line);
                    $commentNode->indent = null;
                    $this->add($realNode);
                    $this->add($commentNode);
            }
        }
    }

    public function build(&$parent = null)
    {
        if (!is_null($this->_tag)) {
            $tagged = TagFactory::transform($this->_tag, $this);
            if ($tagged instanceof Node || $tagged instanceof NodeList) {
                return $tagged->build();
            }
            return $tagged;
        }
        return is_null($this->value) ? Builder::getScalar(trim($this->raw)) : $this->value->build();
    }

    public function getTargetOnLessIndent(Node &$node):Node
    {
        if ($node instanceof NodeScalar || $node instanceof NodeBlank ) {
            return $this->getParent();
        } else {
            return $this->getParent($node->indent);
        }
    }

    public function getTargetOnMoreIndent(Node &$node):Node
    {
        return $this->getParent();
    }
}