<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Builder;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Scalar extends NodeGeneric
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
        if (!is_null($this->tag)) {
            $tagged = TagFactory::transform($this->tag, $this);
            if ($tagged instanceof NodeGeneric || $tagged instanceof NodeList) {
                return $tagged->build();
            }
            return $tagged;
        }
        return is_null($this->value) ? Builder::getScalar(trim($this->raw)) : $this->value->build();
    }

    public function getTargetOnLessIndent(NodeGeneric &$node):NodeGeneric
    {
        if ($node instanceof Scalar || $node instanceof Blank ) {
            return $this->getParent();
        } else {
            return $this->getParent($node->indent);
        }
    }

    public function getTargetOnMoreIndent(NodeGeneric &$node):NodeGeneric
    {
        return $this->getParent();
    }
}