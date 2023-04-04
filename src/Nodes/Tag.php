<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Tag\TagFactory;
use Dallgoot\Yaml\Types\Tagged;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\Nodes\Generic\NodeGeneric;
use Dallgoot\Yaml\Nodes\Generic\Actions;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 * @todo    handle tags like  <tag:clarkevans.com,2002:invoice>
 */
class Tag extends Actions
{

    public function isAwaitingChild(NodeGeneric $node): bool
    {
        return is_null($this->value);
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node): NodeGeneric
    {
        if (is_null($this->value) && $this->indent > 0) {
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
     * @return Tagged|mixed The tag object of class Dallgoot\Yaml\Types\Tagged.
     */
    public function build(&$parent = null)
    {
        if (is_null($this->value) && $this->getParent() instanceof Root) {
            if (!preg_match(Regex::TAG_PARTS, (string) $this->tag, $matches)) {
                throw new \UnexpectedValueException("Tag '$this->tag' is invalid", 1);
            }
            $handle = $matches['handle'];
            $tagname = $matches['tagname'];
            $this->getRoot()->getYamlObject()->addTag($handle, $tagname);
            return;
        }
        $value = $this->value;
        if (is_null($parent) && $value instanceof NodeGeneric && $value->isOneOf('Item', 'Key')) {
            $value = new NodeList(
                /** @scrutinizer ignore-type */
                $value
            );
        }
        try {
            $transformed = TagFactory::transform((string) $this->tag, $value, $parent);
            return $transformed;
        } catch (\UnexpectedValueException $e) {
            return new Tagged((string) $this->tag, is_null($value) ? null : $this->value->build($parent));
        } catch (\Throwable $e) {
            throw new \Exception("Tagged value could not be transformed for tag '$this->tag'", 1, $e);;
        }
    }
}
