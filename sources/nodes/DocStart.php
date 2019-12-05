<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeFactory;
use Dallgoot\Yaml\Regex;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class DocStart extends NodeGeneric
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $rest = substr(ltrim($nodeString), 3);
        if (!empty($rest)) {
            $n = NodeFactory::get($rest, $line);
            $n->indent = null;
            $this->add($n);
        }
    }

    public function add(NodeGeneric $child):NodeGeneric
    {
        if ($this->value instanceof NodeGeneric) {
            return $this->value->add($child);
        } else {
            return parent::add($child);
        }
    }

    public function build(&$parent = null)
    {
        if (is_null($parent)) {
            throw new \Exception(__METHOD__." expects a YamlObject as parent", 1);
        }
        if (!is_null($this->value)) {
            if ($this->value instanceof Tag){
                preg_match(Regex::TAG_PARTS, $this->value->raw, $tagparts);
                if (preg_match("/(?(DEFINE)".Regex::TAG_URI.')(?&url)/', $tagparts['tagname'], $matches)) {
                    // throw new \UnexpectedValueException("Tag '".$this->value->raw."' is invalid", 1);
                    $parent->addTag($tagparts['handle'], $tagparts['tagname']);
                    // var_dump('HERE');
                } else {
                    // var_dump('THERE');
                    $this->value->build($parent);
                }
            } else {
                $text = $this->value->build($parent);
                !is_null($text) && $parent->setText($text);
            }
        }
        return null;
    }

    public function isAwaitingChild(NodeGeneric $node):bool
    {
        return $this->value instanceof NodeGeneric && $this->value->isOneOf('Anchor', 'Literal', 'LiteralFolded');
    }

    public function getTargetOnEqualIndent(NodeGeneric &$node):NodeGeneric
    {
        if ($this->value instanceof NodeGeneric) {
            if ($this->value instanceof Tag) {
                if (!preg_match("/".Regex::TAG_URI."/", $this->value->raw)) {
                    return $this->value;
                }
            } elseif ($this->value->isAwaitingChild($node)) {
                return $this->value;
            }
        }
        return $this->getParent();
    }
}