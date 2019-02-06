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
            $this->add(NodeFactory::get($value, $line));
        }

    }

    public static function buildReference($node, $parent)
    {
        $tmp = is_null($node->value) ? null : $node->value->build($parent);
        if ($node instanceof NodeRefDef) Builder::$_root->addReference($node->identifier, $tmp);
        return Builder::$_root->getReference($node->identifier);
    }
}