<?php

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Yaml as Y, Regex as R};

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Node2PHP
{
    public static $dateAsString = false;
    /**
     * Returns the correct PHP datatype for the value of the current Node
     *
     * @param Node $n a Node object to be evaluated as PHP type.
     * @return mixed  The value as PHP type : scalar, array or Compact, DateTime
     * @throws \Exception if occurs in self::getScalar or self::getCompact
     */
    public static function get(Node $n)
    {
        if (!($n instanceof Node)) {
            throw new Exception("Error: only Dallgoot\\Yaml\\Node is valid argument", 1);
        }
        if (is_null($n->value)) return null;
        if ($n->type & (Y::REF_CALL|Y::SCALAR)) return self::getScalar((string) $n->value);
        if ($n->type & Y::JSON) return $n->value;
        $expected = [Y::QUOTED => substr(trim((string) $n->value), 1, -1),
                     Y::RAW    => strval((string) $n->value)];
        return $expected[$n->type] ?? null;
    }

    /**
     * Returns the correct PHP type according to the string value
     *
     * @param string $v a string value
     *
     * @return mixed The value with appropriate PHP type
     * @throws \Exception if happens in R::isDate or R::isNumber
     */
    public static function getScalar(string $v)
    {
        if (R::isDate($v))   return self::$dateAsString ? $v : date_create($v);
        if (R::isNumber($v)) return self::getNumber($v);
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
     */
    private static function getNumber(string $v)
    {
        if (preg_match(R::OCTAL_NUM, $v)) return intval(base_convert($v, 8, 10));
        if (preg_match(R::HEX_NUM, $v))   return intval(base_convert($v, 16, 10));
        return is_bool(strpos($v, '.')) ? intval($v) : floatval($v);
    }
}