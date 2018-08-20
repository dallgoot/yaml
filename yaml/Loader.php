<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Yaml as Y;

/**
 * TODO
 * @author stephane.rebai@gmail.com
 * @license Apache 2.0
 * @link TODO : url to specific online doc
 */
final class Loader
{
    //public

    /* @var null|string */
    public $error;

    public const EXCLUDE_DIRECTIVES = 1;//DONT include_directive
    public const IGNORE_COMMENTS    = 2;//DONT include_comments
    public const NO_PARSING_EXCEPTIONS = 4;//THROW Exception on parsing Errors
    public const NO_OBJECT_FOR_DATE = 8;//DONT import date strings as dateTime Object
    //privates
    /* @var null|string */
    private $content;
    /* @var null|string */
    private $filePath;
    /* @var integer */
    private $debug = 0;///TODO: determine levels
    /* @var integer */
    private $options = 0;
    //Exceptions messages
    private const INVALID_VALUE        = self::class.": at line %d";
    private const EXCEPTION_NO_FILE    = self::class.": file '%s' does not exists (or path is incorrect?)";
    private const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";
    private const EXCEPTION_LINE_SPLIT = self::class.": content is not a string(maybe a file error?)";

    public function __construct($absolutePath = null, $options = null, $debug = 0)
    {
        $this->debug   = is_int($debug) ? min($debug, 3) : 1;
        $this->options = is_int($options) ? $options : $this->options;
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
     * Parse Yaml lines into a hierarchy of Node
     *
     * @param      string       $strContent  The Yaml string or null to parse loaded content
     * @throws     \Exception    if content is not available as $strContent or as $this->content (from file)
     * @throws     \ParseError  if any error during parsing or building
     *
     * @return     array|YamlObject|null      null on errors if NO_PARSING_EXCEPTIONS is set, otherwise an array of YamlObject or just YamlObject
     */
    public function parse($strContent = null)
    {
        $source = $this->content ?? preg_split("/([^\n\r]+)/um", $strContent, 0, PREG_SPLIT_DELIM_CAPTURE);
        //TODO : be more permissive on $strContent values
        if (!is_array($source) || !count($source)) throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        $previous = $root = new Node();
        $emptyLines = [];
        try {
            $gen = function () use($source) {
                foreach ($source as $key => $value) {
                    yield ++$key => $value;
                }
            };
            foreach ($gen() as $lineNb => $lineString) {
                $n = new Node($lineString, $lineNb);
                if ($n->type & (Y::LITTERALS|Y::BLANK)) {
                    if ($this->onSpecialType($n, $previous, $emptyLines)) continue;
                } else {
                    foreach ($emptyLines as $blankNode) {
                        $blankNode->getParent()->add($blankNode);
                    }
                }
                $emptyLines = [];
                switch ($n->indent <=> $previous->indent) {
                    case -1: $target = $previous->getParent($n->indent);
                        break;
                    case 0:  $target = $previous->getParent();
                        break;
                    default: $target = $previous->type & Y::SCALAR ? $previous->getParent() : $previous;
                }
                if ($this->onContextType($n, $target, $lineString)) continue;
                $target->add($n);
                $previous = $n;
            }
            if ($this->debug === 2) echo "\033[33mParsed Structure\033[0m\n",var_export($root, true);
            $out = Builder::buildContent($root, $this->debug);
            return $out;
        } catch (\Error|\Exception|\ParseError $e) {
            $file = basename($this->filePath);
            $message = basename($e->getFile())."@".$e->getLine().":".$e->getMessage()." in '$file' @".($lineNb)."\n";
            if ($e instanceof \ParseError && ($this->options & self::NO_PARSING_EXCEPTIONS)) {
                trigger_error($message, E_USER_WARNING);
                $this->error = $message;
                return null;
            }
            var_dump($root);
            throw new \Exception($message, 3);
        }
    }

    private function onSpecialType(&$n, &$previous, &$emptyLines):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($n->type & Y::BLANK) {
            if ($previous->type & Y::SCALAR) $emptyLines[] = $n->setParent($previous->getParent());
            if ($deepest->type & Y::LITTERALS) $emptyLines[] = $n->setParent($deepest);
            return true;
        }
        return false;
    }

    private function onContextType(&$n, &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest->type & Y::PARTIAL) {
            //TODO:verify this edge case
            // if ($n->type === Y::KEY && $n->indent === $previous->indent) {
            //     throw new \ParseError(sprintf(self::INVALID_VALUE, $lineNb), 1);
            // }
            $deepest->parse($deepest->value.' '.ltrim($lineString));
            return true;
        }
        if (($previous->type & Y::LITTERALS) || (($deepest->type & Y::LITTERALS) && is_null($deepest->value))) {
            $n->type = Y::SCALAR;
            $n->identifier = null;
            $n->value = trim($lineString);
        }
        if (($deepest->type & (Y::LITTERALS|Y::REF_DEF|Y::SET_VALUE|Y::TAG)) && is_null($deepest->value)) {
            $previous = $deepest;
        }
        return false;
    }
}
