<?php
namespace Dallgoot\Yaml;

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
     * @param Node|null $node (optional) a node that will be pushed as first element
     */
    public function __construct(Node $node = null)
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
        $fqn = __NAMESPACE__."\\$nodeType";
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
            if (!($child instanceof NodeComment)
                && !($child instanceof NodeDirective)
                && !($child instanceof NodeBlank)
                && !($child instanceof NodeDocstart
                && is_null($child->value)) ) return true;
        }
        return false;
    }

    public function push($node)
    {
        $type = null;
        if     ($node instanceof NodeItem )    $type = self::SEQUENCE;
        elseif ($node instanceof NodeKey)      $type = self::MAPPING;
        elseif ($node instanceof NodeSetKey
             || $node instanceof NodeSetValue) {
            $type = self::SET;
        } elseif ($node instanceof NodeScalar ){
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
                return Builder::getScalar($this->buildMultiline());
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
                if ($child instanceof NodeScalar) {
                    $separator = isset($output[-1])  && $output[-1] === "\n" ? '' : ' ';
                    $output .= $separator.trim($child->raw);
                } elseif ($child instanceof NodeBlank) {
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
            if ($child instanceof NodeComment) {
                // $child->build();
            } else {
                if($child->value instanceof NodeComment) {
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
