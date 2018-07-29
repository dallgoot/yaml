<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;

class Loader
{
    //public
    public $errors = [];

    public const EXCLUDE_DIRECTIVES = 1;//DONT include_directive
    public const IGNORE_COMMENTS    = 2;//DONT include_comments
    public const EXCEPTIONS_PARSING = 4;//THROW Exception on parsing Errors
    public const NO_OBJECT_FOR_DATE = 8;//DONT import date strings as dateTime Object
    //privates
    private $content;/* @var null|string */
    private $filePath;/* @var null|string */
    private $debug = 0;//TODO: determine levels
    private $options = 0;/* @var int */
    //Exceptions messages
    private const INVALID_VALUE        = self::class.": at line %d";
    private const EXCEPTION_NO_FILE    = self::class.": file '%s' does not exists (or path is incorrect?)";
    private const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";
    private const EXCEPTION_LINE_SPLIT = self::class.": content is not a string(maybe a file error?)";

    public function __construct($absolutePath = null, $options = null, $debug = 0)
    {
        $this->debug   = is_int($debug)   ? min($debug, 3) : 1;
        $this->options = is_int($options) ? $options       : $this->options;
        if (is_string($absolutePath)) {
            $this->load($absolutePath);
        }
    }

    /**
     * load a file and save its content as $content
     *
     * @param      string       $absolutePath  The absolute path of a file
     *
     * @throws     \Exception   if file don't exist OR reading failed
     *
     * @return     self  ( returns the same Loader  )
     */
    public function load(string $absolutePath):Loader
    {
        $this->debug && var_dump($absolutePath);
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
        $this->content = $content;
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
        $source = $this->content;
        if (is_null($source)) $source = preg_split("/([^\n\r]+)/um", $strContent, 0, PREG_SPLIT_DELIM_CAPTURE);
        //TODO : be more permissive on $strContent values
        if (!is_array($source)) throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        $previous = $root = new Node();
        $emptyLines = [];
        $specialTypes = Y\LITTERALS | Y\BLANK;
        try {
            foreach ($source as $lineNb => $lineString) {
                $n = new Node($lineString, $lineNb + 1);//TODO: useful???-> $this->debug && var_dump($n);
                $parent  = $previous;
                $deepest = $previous->getDeepestNode();
                if ($deepest->type & Y\PARTIAL) {
                    //TODO:verify this edge case
                    // if ($n->type === Y\KEY && $n->indent === $previous->indent) {
                    //     throw new \ParseError(sprintf(self::INVALID_VALUE, $lineNb), 1);
                    // }
                    $deepest->parse($deepest->value.' '.ltrim($lineString));
                } else {
                    if ($n->type & $specialTypes) {
                        if ($this->onSpecialType($n, $parent, $previous, $emptyLines)) continue;
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
                        if ($this->onDeepestType($n, $parent, $previous, $lineString)) continue;
                    }
                    $parent->add($n);
                    $previous = $n;
                }
            }
            if ($this->debug === 2) {
                var_dump("\033[33mParsed Structure\033[0m\n", $root);
                die("Debug of root structure requested (remove debug level to suppress these)");
            }
            $out = Builder::buildContent($root, $this->debug);//var_dump($out);exit();
            return $out;
        } catch (\ParseError $pe) {
            $message = $pe->getMessage()." on line ".$pe->getLine()." for '$this->filePath'\n";
            if ($this->options & self::EXCEPTIONS_PARSING) {
                var_dump($root);
                throw new \Exception($message, 1);
            }
            $this->errors[] = $message;
        } catch (\Error|\Exception $e) {
            throw new \Exception(basename($e->getFile())."(".$e->getLine()."):".$e->getMessage()." for '$this->filePath'\n", 3);
        }
    }

    private function onSpecialType(&$n, &$parent, &$previous, &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($n->type & Y\LITTERALS) {
            if ($deepest->type & Y\KEY && is_null($deepest->value)) {
                $deepest->add($n);
                $previous = $n;
                return true;
            }
        }
        if ($n->type & Y\BLANK) {
            if ($previous->type & Y\SCALAR) $emptyLines[] = $n->setParent($previous->getParent());
            if ($deepest->type & Y\LITTERALS) $emptyLines[] = $n->setParent($deepest);
            return true;
        }
        return false;
    }

    private function onDeepestType(&$n, &$parent, &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest->type & Y\LITTERALS) {
            $n->value = trim($lineString);//fall through
        }
        if ($deepest->type & (Y\LITTERALS | Y\REF_DEF | Y\SET_VALUE | Y\TAG)) {
            $parent = $deepest;
            return false;
        }
        if ($deepest->type & (Y\BLANK | Y\SCALAR) ) {
            if ($n->type === Y\SCALAR && ($deepest->getParent()->type & Y\LITTERALS)) {
                $deepest->type = Y\SCALAR;
                $deepest->value .= "\n".$n->value;
                return true;
            } else {
                if ($previous->type & (Y\ITEM | Y\SET_KEY)) {
                    $parent = $deepest->getParent();
                }
            }
        }
        return false;
    }
}
