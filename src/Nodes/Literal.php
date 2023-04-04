<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml\NodeList;
use Dallgoot\Yaml\Nodes\Generic\Literals;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Literal extends Literals
{
    public function getFinalString(NodeList $list, ?int $refIndent = null): string
    {
        $result = '';
        $list = $list->filterComment();
        if ($this->identifier !== '+') {
            self::litteralStripTrailing($list);
        }
        if ($list->count()) {
            $list->setIteratorMode(NodeList::IT_MODE_DELETE);
            $first  = $list->shift();
            $indent = $refIndent ?? $first->indent;
            $result = $this->getChildValue($first, $indent);
            foreach ($list as $child) {
                $value = "\n";
                if (!($child instanceof Blank)) {
                    $newIndent = $indent > 0 ? $child->indent - $indent : 0;
                    $value .= str_repeat(' ', $newIndent) . $this->getChildValue($child, $indent);
                }
                $result .= $value;
            }
        }
        return $result;
    }
}
