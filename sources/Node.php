<?php

namespace Dallgoot\Yaml;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
class Node
{
    /** @var bool */
    public static $dateAsString = false;

    /** @var null|string|boolean */
    public $identifier;
    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var null|string */
    public $raw;
    /** @var int */
    // public $type;
    /** @var null|Node|NodeList|string */
    public $value;

    /** @var null|Node */
    private $parent;

    /**
     * Create the Node object and parses $nodeString IF not null (else assume a root type Node)
     *
     * @param string|null $nodeString The node string
     * @param int|null    $line       The line
     */
    public function __construct(string $nodeString = null, $line = 0)
    {
        $this->raw = $nodeString;
        $this->line = (int) $line;
         //permissive to tabs but replacement
        $nodeValue = preg_replace("/^\t+/m", " ", $nodeString);
        $this->indent = strspn($nodeValue, ' ');
        // $this->value = $nodeString;
    }

    /**
     * Sets the parent of the current Node
     *
     * @param Node $node The node
     *
     * @return Node|self The currentNode
     */
    public function setParent(Node $node):Node
    {
        $this->parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $parent OR the current Node itself
     *
     * @param int|null $indent The indent
     * @param int $type  first ancestor of this YAML::type is returned
     *
     * @return Node   The parent.
     */
    public function getParent(int $indent = null, $type = 0):Node
    {
        if (!is_int($indent)) return $this->parent ?? $this;
        $cursor = $this;
        while ($cursor instanceof Node
               && $cursor->indent !== $indent
               && !($cursor->parent instanceof NodeRoot)) {
            $cursor = $cursor->parent;
        }
        return $cursor->parent ?? $this;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, push $child into and set NodeList type accordingly
     *
     * @param Node $child The child
     *
     * @todo  refine the conditions when Y::LITTERALS
     */
    public function add(Node $child)
    {
        $child->setParent($this);
        if (is_null($this->value)) {
            $this->value = $child;
        } elseif ($this->value instanceof Node) {
            $this->value = new NodeList($this->value);
            $this->value->push($child);
        } elseif ($this->value instanceof NodeList) {
            $this->value->push($child);
        // } else {
        //     $this->value = new NodeScalar($this->raw, $this->line);
        }
    }

    /**
     * Gets the deepest node.
     *
     * @return Node|self  The deepest node.
     */
    public function getDeepestNode():Node
    {
        $cursor = $this;
        while ($cursor->value instanceof Node) {
            if ($cursor->value instanceof NodeList) {
                if ($cursor->value->count() > 0) {
                    $cursor = $cursor->value->OffsetGet(0);
                } else {
                    // $cursor = $cursor;
                    break;
                }
            } else {
                $cursor = $cursor->value;
            }
        }
        return $cursor;
    }


    /**
     * For certain (special) Nodes types some actions are required BEFORE parent assignment
     *
     * @param Node   $previous   The previous Node
     * @param array  $emptyLines The empty lines
     *
     * @return boolean  if True self::parse skips changing previous and adding to parent
     * @see self::parse
     */
    public function needsSpecialProcess(Node &$previous, array &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest instanceof NodePartial) {
            return $deepest->specialProcess($this, $emptyLines);
        } else {
            return $this->specialProcess($previous, $emptyLines);
        }
    }

    public function specialProcess(Node &$previous, array &$emptyLines)
    {
        // $deepest = $previous->getDeepestNode();
        // //what first character to determine if escaped sequence are allowed
        // $val = ($deepest->value[-1] !== "\n" ? ' ' : '').substr($this->raw, $this->indent);
        // $deepest->parse($deepest->value.$val);
        return false;
    }


    public function getTargetOnLessIndent(Node $previous):Node
    {
        return $previous->getParent($this->indent);
    }

    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetonEqualIndent(Node &$previous):Node
    {
        return $previous->getParent();
    }

   /**
     * Modify parent target when current Node indentation is superior to previous node indentation
     *
     * @param Node $previous The previous
     *
     * @return Node
     */
    public function getTargetOnMoreIndent(Node &$previous):Node
    {
        $target = $previous;
        $deepest = $previous->getDeepestNode();
        if ($deepest->isAwaitingChildren()) {
            $target = $deepest;
        }
        return $target;
    }

    public function isAwaitingChildren()
    {
        return false;
    }

   /**
     * According to the current Node type and deepest value
     * this indicates if self::parse skips (or not) the parent and previous assignment
     *
     * @param  Node     $target    The current Node as target parent
     *
     * @return boolean  True if context, False otherwiser
     * @todo   is this really necessary according ot other checkings out there ?
     */
    public function skipOnContext(Node &$target):bool
    {
        return false;
    }


    public function getValue(&$parent = null)
    {
        if (is_null($this->value))                return null;
        elseif (is_string($this->value))          return $this->getScalar($this->value);
        elseif ($this->value instanceof Node)     return $this->value->getValue($parent);
        elseif ($this->value instanceof NodeList) return Builder::buildNodeList($this->value, $parent);
        else {
            throw new \ParseError("Error trying to getValue of ".gettype($this->value));
        }
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param string $v a string value
     *
     * @return mixed The value with appropriate PHP type
     * @throws \Exception if happens in Regex::isDate or Regex::isNumber
     */
    public static function getScalar(string $v)
    {
        if (Regex::isDate($v))   return self::$dateAsString ? $v : date_create($v);
        if (Regex::isNumber($v)) return self::getNumber($v);
        $types = ['yes'   => true,
                    'no'    => false,
                    'true'  => true,
                    'false' => false,
                    'null'  => null,
                    '.inf'  => INF,
                    '-.inf' => -INF,
                    '.nan'  => NAN
        ];
        return array_key_exists(strtolower($v), $types) ? $types[strtolower($v)] : $v;
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param string $v a string value
     *
     * @return int|float   The scalar value with appropriate PHP type
     * @todo make sure there 's only ONE dot before cosndering a float
     */
    private static function getNumber(string $v)
    {
        if (preg_match(Regex::OCTAL_NUM, $v)) return intval(base_convert($v, 8, 10));
        if (preg_match(Regex::HEX_NUM, $v))   return intval(base_convert($v, 16, 10));
        return is_bool(strpos($v, '.')) ? intval($v) : floatval($v);
    }

    /**
     * Generic function to distinguish between Node and NodeList
     *
     * @param mixed         $parent The parent
     *
     * @return mixed  ( description_of_the_return_value )
     */
    public function build(&$parent = null)
    {
        // if ($this->value instanceof NodeList) return Builder::buildNodeList($this->value, $parent);
        // if ($this->value instanceof Node) return $this->build($parent);
        // return self::build($parent);
        return $this->getValue($this->value);
    }


    /**
     * PHP internal function for debugging purpose : simplify output provided by 'var_dump'
     *
     * @return array  the Node properties and respective values displayed by 'var_dump'
     */
    public function __debugInfo():array
    {
        $props = ['line'  => $this->line,
                'indent'=> $this->indent,
                'value' => $this->value,
                'raw'   => $this->raw,
            ];
        if ($this->identifier) $props['identifier'] = "($this->identifier)";
        return $props;
    }
}
