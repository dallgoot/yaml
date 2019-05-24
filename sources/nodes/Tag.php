<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\TagFactory;
use Dallgoot\Yaml\Tagged;
use Dallgoot\Yaml\Regex;

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
            if (!preg_match(Regex::TAG_PARTS, $this->tag, $matches)) {
                throw new \UnexpectedValueException("Tag '$this->tag' is invalid", 1);
            }
                    // var_dump($matches['handle'], $matches['tagname']);
            $handle = $matches['handle'];
            $tagname = $matches['tagname'];
            $this->getRoot()->getYamlObject()->addTag($handle, $tagname);
            return;
        }
        $value = $this->value;
        if (is_null($parent) && $value->isOneOf('Item', 'Key')) {
            $value = new NodeList(/** @scrutinizer ignore-type */ $value);
        }
        // if (TagFactory::isKnown((string) $this->tag)) {
        try {
            if ($value instanceof Literals) {
                $value = $value->value;
            }
            $transformed = TagFactory::transform((string) $this->tag, $value);
            if ($transformed instanceof NodeGeneric || $transformed instanceof NodeList) {
                return $transformed->build($parent);
            }
            return $transformed;
        // } else {
        } catch (\Throwable $e) {
            if ($e instanceof \InvalidValueException) {
                return new Tagged($this->tag, is_null($value) ? null : $value->build($parent));
            }
            throw new \Exception("Can NOT build Tag", 1, $e);
        }

    }
}