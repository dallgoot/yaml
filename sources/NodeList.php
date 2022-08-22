<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes\NodeGeneric;
use Dallgoot\Yaml\Nodes\Blank;
use Dallgoot\Yaml\Nodes\Comment;
use Dallgoot\Yaml\Nodes\Directive;
use Dallgoot\Yaml\Nodes\Docstart;
use Dallgoot\Yaml\Nodes\Item;
use Dallgoot\Yaml\Nodes\Key;
use Dallgoot\Yaml\Nodes\SetKey;
use Dallgoot\Yaml\Nodes\SetValue;
use Dallgoot\Yaml\Nodes\Scalar;


/**
 * A collection of Nodes
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class NodeList extends \SplDoublyLinkedList
{
    const MAPPING   = 1;
    const MULTILINE = 2;
    const SEQUENCE  = 4;
    const SET       = 8;

    public $type;

    /**
     * NodeList constructor
     *
     * @param NodeGeneric|null $node (optional) a node that will be pushed as first element
     */
    public function __construct(NodeGeneric $node = null)
    {
        // parent::__construct();
        // $this->setIteratorMode(self::IT_MODE_KEEP);
        if (!is_null($node)) {
            $this->push($node);
        }
    }

    public function has(string $nodeType):bool
    {
        $tmp = clone $this;
        $tmp->rewind();
        $fqn = __NAMESPACE__."\\Nodes\\$nodeType";
        foreach ($tmp as $child) {
            if ($child instanceof $fqn) return true;
        }
        return false;
    }

    public function hasContent():bool
    {
        $tmp = clone $this;
        $tmp->rewind();
        foreach ($tmp as $child) {
            if (!($child instanceof Comment)
                && !($child instanceof Directive)
                && !($child instanceof Blank)
                && !($child instanceof Docstart
                && is_null($child->value)) ) return true;
        }
        return false;
    }

    public function push($node): void
    {
        $type = null;
        if ($node instanceof Item ) {
            $type = self::SEQUENCE;
        } elseif ($node instanceof Key) {
            $type = self::MAPPING;
        } elseif ($node->isOneOf('SetKey','SetValue')) {
            $type = self::SET;
        } elseif ($node instanceof Scalar){
            $type = self::MULTILINE;
        }
        if (!is_null($type) && $this->checkTypeCoherence($type)) {
            $this->type = $type;
        }
        parent::push($node);
    }

    /**
     * Verify that the estimated type is coherent with this list current $type
     *
     * @param      int      $estimatedType  The estimated type
     *
     * @return     boolean  True if coherent, False otherwise
     * @todo       implement invalid cases
     */
    public function checkTypeCoherence($estimatedType):bool
    {
       // if ($this->type === self::MAPPING) {
       //     if ($estimatedType === self::SEQUENCE) {
       //         throw new \ParseError("Error : no coherence in types", 1);
       //     }
       // }
       return (bool) $estimatedType;
    }

    public function build(&$parent = null)
    {
        switch ($this->type) {
            case self::MAPPING:  //fall through
            case self::SET:
                $collect = $parent ?? new \StdClass;
                return $this->buildList($collect);
            case self::SEQUENCE:
                $collect = $parent ?? [];
                return $this->buildList($collect);
            default:
                $this->filterComment();
                // return Nodes\Scalar::getScalar($this->buildMultiline());
                return (new Nodes\Scalar('', 0))->getScalar($this->buildMultiline(), true);
        }
    }

    public function buildList(&$collector)
    {
        $this->rewind();
        foreach ($this as $child) {
            $child->build($collector);
        }
        return $collector;
    }

    public function buildMultiline():string
    {
        $output = '';
        $list = clone $this;
        if ($list->count() > 0) {
            $list->rewind();
            $first = $list->shift();
            $output = trim($first->raw);
            foreach ($list as $child) {
                if ($child instanceof Scalar) {
                    $separator = isset($output[-1])  && $output[-1] === "\n" ? '' : ' ';
                    $output .= $separator.trim($child->raw);
                } elseif ($child instanceof Blank) {
                    $output .= "\n";
                } else {
                    $child->build();
                }
            }
        }
        return trim($output);
    }

    /**
     * Remove NodeComment and returns a new one
     *
     * @return   NodeList  a new NodeList without NodeComment in it
     * @todo     double check that NodeComment are built
     */
    public function filterComment():NodeList
    {
        $this->rewind();
        $out = new NodeList;
        foreach ($this as $index => $child) {
            if ($child instanceof Comment) {
                // $child->build();
            } else {
                if($child->value instanceof Comment) {
                    // $child->value->build();
                    // $child->value = null;
                } elseif($child->value instanceof NodeList) {
                    $child->value = $child->value->filterComment();
                }
                $out->push($child);
            }
        }
        // $this->rewind();
        $out->rewind();
        return $out;
    }

    /**
     * Provides a slimmer output when using var_dump Note: currently PHP ignores it on SPL types
     * @todo activate when PHP supports it
     */
    // public function __debugInfo()
    // {
    //     return ['type'=> Y::getName($this->type), 'dllist'=> $this->dllist];
    // }
}
