<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Yaml as Y;
use Dallgoot\Yaml\Regex as R;

/**
 * TODO
 * @category tag in class comment
 * @package tag in class comment
 * @author tag in class comment
 * @license tag in class comment
 */
final class Node
{
    /** @var int */
    public $indent = -1;
    /** @var int */
    public $line;
    /** @var int */
    public $type;
    /** @var null|string|boolean */
    public $identifier;
    /** @var Node|NodeList|null|string */
    public $value;

    /** @var null|Node */
    private $parent;

    public function __construct($nodeString = null, $line = null)
    {
        $this->line = $line;
        if (is_null($nodeString)) {
            $this->type = Y::ROOT;
        } else {
            $this->parse($nodeString);
        }
    }

    /**
     * Sets the parent of the current Node
     * @param      Node       $node   The node
     *
     * @return     Node|self  The currentNode
     */
    public function setParent(Node $node):Node
    {
        $this->parent = $node;
        return $this;
    }

    /**
     * Gets the ancestor with specified $indent or the direct $parent OR the current Node itself
     *
     * @param int|null    $indent        The indent
     *
     * @return Node|self        The parent.
     */
    public function getParent(int $indent = null):Node
    {
        if (!is_int($indent)) return $this->parent ?? $this;
        $cursor = $this;
        while ($cursor instanceof Node && $cursor->indent >= $indent) {
            $cursor = $cursor->parent;
        }
        return $cursor;
    }

    /**
     * Set the value for the current Node :
     * - if value is null , then value = $child (Node)
     * - if value is Node, then value is a NodeList with (previous value AND $child)
     * - if value is a NodeList, simply push $child into
     *
     * @param      Node  $child  The child
     */
    public function add(Node $child):void
    {
        $child->setParent($this);
        $current = $this->value;
        if (is_null($current)) {
            $this->value = $child;
            return;
        } else {
            if (is_scalar($current)) {
                $current = new Node($current, $this->line);
            }
            if ($current instanceof Node) {
                $this->value = new NodeList();
                $this->value->push($current);
            }
            $this->value->push($child);
            //modify type according to child
            switch ($child->type) {
                case Y::COMMENT: //fall through
                case Y::KEY:     $this->value->type = Y::MAPPING;break;
                case Y::ITEM:    $this->value->type = Y::SEQUENCE;break;
            }
            $this->type & Y::LITTERALS && $this->value->type = $this->type;
        }
    }

    public function getDeepestNode():Node
    {
        $cursor = $this;
        while ($cursor->value instanceof Node) {
            $cursor = $cursor->value;
        }
        return $cursor;
    }

    /**
     * Parses the string (assumed to be a line from a valid YAML)
     *
     * @param      string     $nodeString  The node string
     *
     * @return     Node|self  ( description_of_the_return_value )
     */
    public function parse(string $nodeString):Node
    {
        $nodeValue = preg_replace("/^\t+/m", " ", $nodeString);//permissive to tabs but replacement
        $this->indent = strspn($nodeValue, ' ');
        $nodeValue = ltrim($nodeValue);
        if ($nodeValue === '') {
            $this->type = Y::BLANK;
            // $this->indent = 0; // remove if no bugs
        } elseif (substr($nodeValue, 0, 3) === '...') {//TODO: can have something on same line ?
            $this->type = Y::DOC_END;
        } elseif (preg_match(R::KEY, $nodeValue, $matches)) {
            $this->onKey($matches);
        } else {//NOTE: can be of another type according to parent
            list($this->type, $value) = $this->define($nodeValue);
            is_object($value) ? $this->add($value) : $this->value = $value;
        }
        return $this;
    }

    /**
     *  Set the type and value according to first character
     *
     * @param      string  $nodeValue  The node value
     * @return     array   contains [node->type, node->value]
     */
    private function define($nodeValue):array
    {
        $v = substr($nodeValue, 1);
        $first = $nodeValue[0];
        if (in_array($first, ['"', "'"])) {
            $type = R::isProperlyQuoted($nodeValue) ? Y::QUOTED : Y::PARTIAL;
            return [$type, $nodeValue];
        }
        if (in_array($first, ['{', '[']))      return $this->onObject($nodeValue);
        if (in_array($first, ['!', '&', '*'])) return $this->onNodeAction($nodeValue);
        switch ($first) {
            case '#': return [Y::COMMENT, ltrim($v)];
            case "-": return $this->onHyphen($nodeValue);
            case '%': return [Y::DIRECTIVE, ltrim($v)];
            case '?': return [Y::SET_KEY,   empty($v) ? null : new Node(ltrim($v), $this->line)];
            case ':': return [Y::SET_VALUE, empty($v) ? null : new Node(ltrim($v), $this->line)];
            case '>': return [Y::LITT_FOLDED, null];
            case '|': return [Y::LITT, null];
            default:
                return [Y::SCALAR, $nodeValue];
        }
    }

    /**
     * Process when a "key: value" syntax is found in the parsed string
     * Note : key is match 1, value is match 2 as per regex from R::KEY
     * @param      array  $matches  The matches provided by 'preg_match' function
     */
    private function onKey(array $matches):void
    {
        $this->type = Y::KEY;
        $this->identifier = trim($matches[1]);
        $value = isset($matches[2]) ? trim($matches[2]) : null;
        if (!empty($value)) {
            $n = new Node($value, $this->line);
            $hasComment = strpos($value, ' #');
            if (!is_bool($hasComment)) {
                $tmpNode = new Node(trim(substr($value, 0, $hasComment)), $this->line);
                if ($tmpNode->type !== Y::PARTIAL) {
                    $comment = new Node(trim(substr($value, $hasComment+1)), $this->line);
                    $comment->identifier = true;//to specify it is NOT a fullline comment
                    $this->add($comment);
                    $n = $tmpNode;
                }
            }
            $n->indent = $this->indent + strlen($this->identifier);
            $this->add($n);
        }
    }

    /**
     * Determines the correct type and value when a short object/array syntax is found
     *
     * @param      string  $value  The value assumed to start with { or ( or characters
     *
     * @return     array   array with the type and $value (unchanged for now)
     * @see self:define
     */
    private function onObject($value):array
    {
        json_decode($value, false, 512, JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if (json_last_error() === JSON_ERROR_NONE)  return [Y::JSON, $value];
        if (preg_match(R::MAPPING, $value))         return [Y::COMPACT_MAPPING, $value];
        if (preg_match(R::SEQUENCE, $value))        return [Y::COMPACT_SEQUENCE, $value];
        return [Y::PARTIAL, $value];
    }

    /**
     * Determines type and value when an hyphen "-" is found
     *
     * @param      string $nodeValue  The node value
     *
     * @return     array   array with the type and $value
     */
    private function onHyphen($nodeValue):array
    {
        if (substr($nodeValue, 0, 3) === '---') {
            $rest = trim(substr($nodeValue, 3));
            if (empty($rest)) return [Y::DOC_START, null];
            $n = new Node($rest, $this->line);
            $n->indent = $this->indent + 4;
            return [Y::DOC_START, $n->setParent($this)];
        }
        if (preg_match(R::ITEM, $nodeValue, $matches)) {
            if (isset($matches[1]) && !empty(trim($matches[1]))) {
                $n = new Node(trim($matches[1]), $this->line);
                return [Y::ITEM, $n->setParent($this)];
            }
            return [Y::ITEM, null];
        }
        return [Y::SCALAR, $nodeValue];
    }

    /**
     * Determines the type and value according to $nodeValue when one of these characters is found : !,&,*
     *
     * @param      string  $nodeValue  The node value
     *
     * @return     array   array with the type and $value
     * @see self::define
     */
    private function onNodeAction($nodeValue):array
    {
        // TODO: handle tags like  <tag:clarkevans.com,2002:invoice>
        $v = substr($nodeValue, 1);
        $type = ['!' => Y::TAG, '&' => Y::REF_DEF, '*' => Y::REF_CALL][$nodeValue[0]];
        $pos = strpos($v, ' ');
        $this->identifier = is_bool($pos) ? $v : strstr($v, ' ', true);
        $n = is_bool($pos) ? null : (new Node(trim(substr($nodeValue, $pos+1)), $this->line))->setParent($this);
        return [$type, $n];
    }

    /**
     * Returns the correct PHP datatype for the value of the current Node
     *
     * @return     mixed  The value as PHP type : scalar, array or Compact, DateTime
     */
    public function getPhpValue()
    {
        $v = &$this->value;
        if (is_null($v)) return null;
        if ($this->type & (Y::REF_CALL | Y::SCALAR)) return self::getScalar($v);
        if ($this->type & (Y::COMPACT_MAPPING | Y::COMPACT_SEQUENCE)) return self::getCompact(substr($v, 1, -1), $this->type);
        switch ($this->type) {
            case Y::JSON:   return json_decode($v, false, 512, JSON_PARTIAL_OUTPUT_ON_ERROR);
            case Y::QUOTED: return substr($v, 1, -1);
            case Y::RAW:    return strval($v);
            default:
                trigger_error("Error can not get PHP type for ".Y::getName($this->type), E_USER_WARNING);
                return null;
        }
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param      string  $v      a string value
     *
     * @return     mixed   The value with appropriate PHP type
     */
    private static function getScalar(string $v)
    {
        $types = ['yes'   => true,
                  'no'    => false,
                  'true'  => true,
                  'false' => false,
                  'null'  => null,
                  '.inf'  => INF,
                  '-.inf' => -INF,
                  '.nan'  => NAN
        ];
        if (isset($types[strtolower($v)])) return $types[strtolower($v)];
        if (R::isDate($v))   return date_create($v);
        if (R::isNumber($v)) return self::getNumber($v);
        return strval($v);
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param      string  $v      a string value
     *
     * @return     int|float   The scalar value with appropriate PHP type
     */
    private static function getNumber(string $v)
    {
        if (preg_match("/^(0o\d+)$/i", $v))      return intval(base_convert($v, 8, 10));
        if (preg_match("/^(0x[\da-f]+)$/i", $v)) return intval(base_convert($v, 16, 10));
        // TODO: remove these if not needed
        // if preg_match("/^([\d.]+e[-+]\d{1,2})$/", $v)://fall through
        // if preg_match("/^([-+]?(?:\d+|\d*.\d+))$/", $v):
            return is_bool(strpos($v, '.')) ? intval($v) : floatval($v);
    }

    private static function getCompact(string $mappingOrSeqString, int $type):object
    {
        $out = new Compact();
        if ($type === Y::COMPACT_SEQUENCE) {
            $f = function ($e) { return self::getScalar(trim($e));};
            //TODO : that's not robust enough, improve it
            foreach (array_map($f, explode(",", $mappingOrSeqString)) as $key => $value) {
                $out[$key] = $value;
            }
        }
        if ($type === Y::COMPACT_MAPPING) {
            //TODO : that's not robust enough, improve it
            foreach (explode(',', $mappingOrSeqString) as $value) {
                list($keyName, $keyValue) = explode(':', $value);
                $out->{trim($keyName)} = self::getScalar(trim($keyValue));
            }
        }
        return $out;
    }

    /**
     * PHP internal function for debugging purpose : simplify output provided by 'var_dump'
     *
     * @return     array  the Node properties and respective values displayed by 'var_dump'
     */
    public function __debugInfo():array
    {
        return ['line'  => $this->line,
                'indent'=> $this->indent,
                'type'  => Y::getName($this->type).($this->identifier ? "($this->identifier)" : ''),
                'value' => $this->value];
    }
}
