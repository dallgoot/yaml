<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeRaw extends NodeLiterals
{
    public static function buildRaw(NodeList &$list):string
    {
        $result = '';
        if ($list->count()) {
            self::litteralStripTrailing($list);
            $first = $list->shift();
            $refIndent = $first->indent ?? 0;
            $result = substr($first->raw, $first->indent);
            foreach ($list as $key => $child) {
                if ($child->value instanceof NodeList) {
                    $val = parent::buildLitt($child->value);
                } else {
                    if ($child instanceof NodeComment && !$child->identifier) {
                        Builder::$_root->addComment($child->line, $child->value);
                    }
                    $val = $child instanceof NodeBlank ? "" : substr($child->raw, $refIndent);
                }
                $result .= $val;
            }
        }
        return $result;
    }
}