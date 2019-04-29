<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\Tagged;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 * @todo    handle tags like  <tag:clarkevans.com,2002:invoice>
 */
class Tag extends Actions
{

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        return is_null($this->value);
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node):NodeGeneric
    {
        if (is_null($this->value)) {
            return $this;
        } else {
            return $this->getParent();
        }
    }

    /**
     * Builds a tag and its value (also built) and encapsulates them in a Tag object.
     *
     * @param array|object|null $parent The parent
     *
     * @return Tagged|mixed The tag object of class Dallgoot\Yaml\Tagged.
     */
    public function build(&$parent = null)
    {
        if (is_null($this->value) && $this->getParent() instanceof Root) {
            $this->getRoot()->getYamlObject()->addTag($this->tag);
            return;
        }
        $value = $this->value;
        if (is_null($parent) && $value->isOneOf(['Item', 'Key'])) {
            $value = new NodeList(/** @scrutinizer ignore-type */ $value);
        }
        if (TagFactory::isKnown((string) $this->tag)) {
            if ($value instanceof Literals) {
                $value = $value->value;
            }
            $built = TagFactory::transform((string) $this->tag, $value);
            if ($built instanceof NodeGeneric || $built instanceof NodeList) {
                return $built->build($parent);
            }
            return $built;
        } else {
            return new Tagged($this->tag, is_null($value) ? null : $value->build($parent));
        }
    }
}