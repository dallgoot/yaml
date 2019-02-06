<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeLit extends NodeLiterals
{

    public static function buildLitt(NodeList &$list, $modifier = null):string
    {
        $result = '';
        if ($list->count()) {
            if ($modifier !== '+') {
                 self::litteralStripLeading($list);
                 self::litteralStripTrailing($list);
            }
            $first = $list->shift();
            $refIndent = $first->indent ?? 0;
            $result = substr($first->raw, $first->indent);
            $list->setIteratorMode(NodeList::IT_MODE_DELETE);
            foreach ($list as $key => $child) {
                $noMoreContent = $list->has('NodeBlank');
                if ($child->value instanceof NodeList) {
                    $val = self::buildLitt($child->value);
                } else {
                    if ($child instanceof NodeBlank) {
                        $val = $noMoreContent ? "\n" : "";
                    } else {
                        $val = substr($child->raw, $refIndent);
                    }
                }
                $result .= "\n".$val;
            }
        }
        return $result;
    }

}