<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml as Y;

final class Loader
{
    //public
    public $errors = [];

    public const EXCLUDE_DIRECTIVES = 1;//DONT include_directive
    public const IGNORE_COMMENTS    = 2;//DONT include_comments
    public const NO_PARSING_EXCEPTIONS = 4;//THROW Exception on parsing Errors
    public const NO_OBJECT_FOR_DATE = 8;//DONT import date strings as dateTime Object
    //privates
    /* @var null|string */
    private $content;
    /* @var null|string */
    private $filePath;
    /* @var int */
    private $debug = 0;///TODO: determine levels
    /* @var int */
    private $options = 0;
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
        $this->debug > 1 && var_dump($absolutePath);
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
                $deepest = $previous->getDeepestNode();
                if ($deepest->type & Y\PARTIAL) {
                    //TODO:verify this edge case
                    // if ($n->type === Y\KEY && $n->indent === $previous->indent) {
                    //     throw new \ParseError(sprintf(self::INVALID_VALUE, $lineNb), 1);
                    // }
                    $deepest->parse($deepest->value.' '.ltrim($lineString));
                } else {
                    if (($n->type & $specialTypes) && $this->onSpecialType($n, $previous, $emptyLines)) {
                        continue;
                    }
                    if ($n->type & Y\SCALAR) {
                        foreach ($emptyLines as $blankNode) {
                            $blankNode->getParent()->add($blankNode);
                        }
                    }
                    $emptyLines = [];
                    switch ($n->indent <=> $previous->indent) {
                        case -1: $previous->getParent($n->indent)->add($n);break;
                        case 0: $previous->getParent()->add($n);break;
                        default:
                            if ($this->onDeepestType($n, $previous, $lineString)) continue 2;
                            $previous->add($n);
                    }
                    $previous = $n;
                }
            }
            if ($this->debug === 2) {
                var_dump("\033[33mParsed Structure\033[0m\n", $root);
                die("Debug of root structure requested (remove debug level to suppress this)");
            }
            $out = Builder::buildContent($root, $this->debug);
            return $out;
        } catch (\Error|\Exception|\ParseError $e) {
            $file = basename($this->filePath);
            $message = basename($e->getFile())."@".$e->getLine().":".$e->getMessage()." in '$file' @".($lineNb+1)."\n";
            if ($e instanceof \ParseError && ($this->options & self::NO_PARSING_EXCEPTIONS)) {
                trigger_error($message, E_USER_WARNING);
                $this->errors[] = $message;
                return null;
            }
            var_dump($root);
            throw new \Exception($message, 3);
        }
    }

    private function onSpecialType(&$n, &$previous, &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($n->type & Y\KEY && $previous->type & Y\ITEM) {

        }
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

    private function onDeepestType(&$n, &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        // if ($deepest->type & Y\LITTERALS) {
        //     $n->value = trim($lineString);//fall through
        // }
        if (($n->type & Y\SCALAR) && ($deepest->type & (Y\LITTERALS | Y\REF_DEF | Y\SET_VALUE)) && is_null($deepest->value)) {
            $previous = $deepest;
        }
        if (($deepest->type & Y\TAG) && is_null($deepest->value)) {
            $previous = $deepest;
        }
        // // if ($previous->type & Y\ITEM && $n->type & Y\KEY) {
        // //     $previous
        // // }
        if ($deepest->type & (Y\BLANK | Y\SCALAR)) {//|| ($previous->type & (Y\ITEM | Y\SET_KEY))) {
                $previous = $deepest->getParent();
        //     // if ($n->type === Y\SCALAR && ($deepest->getParent()->type & Y\LITTERALS)) {
        //     //     // $deepest->type = Y\SCALAR;
        //     //     // $deepest->value .= "\n".$n->value;
        //     //     // ->add($n);
        //     //     // return true;
        //     // } else {
        //     //     if ($previous->type & (Y\ITEM | Y\SET_KEY)) {
        //     //         $parent = $deepest->getParent();
        //     //     }
        //     // }
        }
        return false;
    }
}
