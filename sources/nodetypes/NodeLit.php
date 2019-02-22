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
    /**
     * Gets the final string.
     *
     * @param      NodeList  $list   The list
     *
     * @return     string    The final string.
     */
    public function getFinalString(NodeList $list, $refIndent = null):string
    {
        $result = '';
        if (!is_null($list) && $list->count()) {
            if ($this->identifier !== '+') {
                 self::litteralStripTrailing($list);
            }
            $first = $list->shift();
            $indent = $refIndent ?? $first->indent;
            $result = $this->getChildValue($first, $indent);
            $list->setIteratorMode(NodeList::IT_MODE_DELETE);
            foreach ($list->filterComment() as $key => $child) {
                $result .= $child instanceof NodeBlank ? "\n" : "\n".$this->getChildValue($child, $indent);
            }
        }
        return $result;
    }
}