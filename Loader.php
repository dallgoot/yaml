<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\{Node as Node, Types as T, Builder};

class Loader
{
    public $errors = [];
    //options
    public const EXCLUDE_DIRECTIVES = 0001;//DONT include_directive
    public const IGNORE_COMMENTS    = 0010;//DONT include_comments
    public const EXCEPTIONS_PARSING = 0100;//THROW Exception on parsing Errors
    public const NO_OBJECT_FOR_DATE = 1000;//DONT import date strings as dateTime Object
    //
    private $_content;
    private $filePath;
    private $_debug   = 0;//TODO: determine levels
    private $_options = 0;
    //Exceptions
    const INVALID_VALUE        = self::class.": at line %d";
    const EXCEPTION_NO_FILE    = self::class.": file '%s' does not exists (or path is incorrect?)";
    const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";
    const EXCEPTION_LINE_SPLIT = self::class.": content is not a string(maybe a file error?)";

    public function __construct($absolutePath = null, $options = null, $debug = 0)
    {
        $this->_debug = is_int($debug) ? min($debug, 3) : 1;
        if (!is_null($options)) {
            $this->options = $options;
        }
        if (!is_null($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    public function load(String $absolutePath):Loader
    {
        $this->_debug && var_dump($absolutePath);
        $this->filePath = $absolutePath;
        if (!file_exists($absolutePath)) {
            throw new \Exception(sprintf(self::EXCEPTION_NO_FILE, $absolutePath));
        }
        $adle = "auto_detect_line_endings";
        $prevADLE = ini_get($adle);
        !$prevADLE && ini_set($adle, "true");
        $content = file($absolutePath, FILE_IGNORE_NEW_LINES);
        !$prevADLE && ini_set($adle, "false");
        if (is_bool($content)) {
            throw new \Exception(sprintf(self::EXCEPTION_READ_ERROR, $absolutePath));
        }
        $this->_content = $content;
        return $this;
    }

    /**
     * Parse Yaml lines into an hierarchy of Node
     *
     * @param      string       $strContent  The Yaml string or null to parse loaded content
     * @throws     \Exception    if content is not available as $strContent or as $this->content (from file)
     * @throws     \ParseError  if any error during parsing or building
     *
     * @return     array|YamlObject      the hierarchy built an array of YamlObject or just YamlObject
     */
    public function parse($strContent = null)
    {
        $source = $this->_content;
        if (is_null($source)) $source = preg_split("/([^\n\r]+)/um", $strContent, 0, PREG_SPLIT_DELIM_CAPTURE);
        //TODO : be more permissive on $strContent values
        if (!is_array($source)) throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        $previous = $root = new Node();
        $emptyLines = [];
        $specialTypes = [T::LITTERAL, T::LITTERAL_FOLDED, T::EMPTY];
        try {
            foreach ($source as $lineNb => $lineString) {
                $n = new Node($lineString, $lineNb + 1);//TODO: useful???-> $this->_debug && var_dump($n);
                $parent  = $previous;
                $deepest = $previous->getDeepestNode();
                if ($deepest->type === T::PARTIAL) {
                    //TODO:verify this edge case
                    // if ($n->type === T::KEY && $n->indent === $previous->indent) {
                    //     throw new \ParseError(sprintf(self::INVALID_VALUE, $lineNb), 1);
                    // }
                    $deepest->parse($deepest->value.' '.ltrim($lineString));
                } else {
                    if (in_array($n->type, $specialTypes)) {
                        if ($this->_onSpecialType($n, $parent, $previous, $emptyLines)) continue;
                    }
                    foreach ($emptyLines as $key => $node) {
                        $node->getParent()->add($node);
                    }
                    $emptyLines = [];
                    if ($n->indent < $previous->indent) {
                        $parent = $previous->getParent($n->indent);
                    } elseif ($n->indent === $previous->indent) {
                        $parent = $previous->getParent();
                    } elseif ($n->indent > $previous->indent) {
                        if ($this->_onDeepestType($n, $parent, $previous, $lineString)) continue;
                    }
                    $parent->add($n);
                    $previous = $n;
                }
            }
            if ($this->_debug === 2) {
                var_dump("\033[33mParsed Structure\033[0m\n", $root);
                exit(0);
            }
            $out = Builder::buildContent($root, $this->_debug);
        } catch (\ParseError $pe) {
            $message = $pe->getMessage()." on line ".$pe->getLine();
            if ($this->_options & self::EXCEPTIONS_PARSING) {
                var_dump($root);
                throw new \Exception($message, 1);
            }
            $this->errors[] = $message;
        } catch (\Error|\Exception $e) {
            throw new \Exception($e->getMessage()." for '$this->filePath'", 1);
        }
        return $out;
    }

    private function _onSpecialType(&$n, &$parent, &$previous, &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        switch ($n->type) {
            case T::EMPTY:
                if ($previous->type === T::SCALAR) $emptyLines[] = $n->setParent($previous->getParent());
                if (in_array($deepest->type, T::$LITTERALS)) $emptyLines[] = $n->setParent($deepest);
                return true;
                break;
            case T::LITTERAL://fall through
            case T::LITTERAL_FOLDED://var_dump($deepest);exit();
                if ($deepest->type === T::KEY && is_null($deepest->value)) {
                    $deepest->add($n);
                    $previous = $n;
                    return true;
                }
            default:
                return false;
        }
    }

    private function _onDeepestType(&$n, &$parent, &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        switch ($deepest->type) {
            case T::LITTERAL:
            case T::LITTERAL_FOLDED:
                $n->value = trim($lineString);//fall through
            case T::REF_DEF://fall through
            case T::SET_VALUE://fall through
            case T::TAG:
                $parent = $deepest;
                break;
            case T::EMPTY:
            case T::SCALAR:
                if ($n->type === T::SCALAR &&
                    !in_array($deepest->getParent()->type, T::$LITTERALS) ) {
                    $deepest->type = T::SCALAR;
                    $deepest->value .= PHP_EOL.$n->value;
                    return true;
                } else {
                    if (!in_array($previous->type, [T::ITEM, T::SET_KEY])) {
                        $parent = $deepest->getParent();
                    }
                }
        }
        return false;
    }

}
