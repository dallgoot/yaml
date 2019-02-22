<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeActions extends Node
{
    public function __construct(string $nodeString, int $line)
    {
        parent::__construct($nodeString, $line);
        $v = substr($nodeString, 1);
        $this->identifier = $v;
        $pos = strpos($v, ' ');
        if (is_int($pos)) {
            $this->identifier = strstr($v, ' ', true);
            $value = trim(substr($nodeString, $pos + 1));
            $value = Regex::isProperlyQuoted($value) ? trim($value, "\"'") : $value;
            $child = NodeFactory::get($value, $line);
            $child->indent = null;
            $this->add($childw);
        }

    }

    public function build(&$parent = null)
    {
        $tmp = is_null($this->value) ? null : $this->value->build($parent);
        $yamlObject = $this->getRoot()->getYamlObject();
        if ($this instanceof NodeRefDef) $yamlObject->addReference($this->identifier, $tmp);
        return $yamlObject->getReference($this->identifier);
    }
}