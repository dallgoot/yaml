<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class NodeLitFolded extends NodeLiterals
{
    /**
     * @param NodeList $list The children
     *
     * @return string    The litteral.
     * @todo   Example 6.1. Indentation Spaces  spaces must be considered as content
     */
    public function getFinalString(NodeList $list):string
    {
        $result = '';
        if ($list->count()) {
            if ($this->identifier !== '+') {
                 self::litteralStripLeading($list);
                 self::litteralStripTrailing($list);
            }
            $first = $list->shift();
            $refIndent = $first->indent ?? 0;
            $refSeparator = ' ';
            $result = substr($first->raw, $first->indent);
            foreach ($list as $child) {
                if($child->indent > $refIndent || ($child instanceof NodeBlank)) {
                    $separator = "\n";
                } else {
                    $separator = !empty($result) && $result[-1] === "\n" ? '' : $refSeparator;
                }
                $val = '';
                if ($child->value instanceof NodeList) {
                    $val = "\n".$this->getFinalString($child->value);
                } else {
                    if ($child instanceof NodeScalar) {
                        $val = $child->build();
                    }
                }
                $result .= $separator .$val;
            }
        }
        return $result;
    }
}