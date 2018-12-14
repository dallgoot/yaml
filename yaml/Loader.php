<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Yaml as Y;

/**
 * TODO
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    TODO : url to specific online doc
 */
final class Loader
{
    //public

    /* @var null|string */
    public static $error;

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
     * Load a file and save its content as $content
     *
     * @param string $absolutePath The absolute path of a file
     *
     * @throws \Exception if file don't exist OR reading failed
     *
     * @return self  ( returns the same Loader  )
     */
    public function load(string $absolutePath):Loader
    {
        if (!file_exists($absolutePath)) {
            throw new \Exception(sprintf(self::EXCEPTION_NO_FILE, $absolutePath));
        }
        $this->filePath = $absolutePath;
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
     * @param string $strContent The Yaml string or null to parse loaded content
     *
     * @throws \Exception    if content is not available as $strContent or as $this->content (from file)
     * @throws \ParseError  if any error during parsing or building
     *
     * @return array|YamlObject|null      null on errors if NO_PARSING_EXCEPTIONS is set, otherwise an array of YamlObject or just YamlObject
     */
    public function parse($strContent = null)
    {
        $source = $this->content ?? preg_split("/\n/m", preg_replace('/(\r\n|\r)/', "\n", $strContent), 0, PREG_SPLIT_DELIM_CAPTURE);
        //TODO : be more permissive on $strContent values
        if (!is_array($source) || !count($source)) throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        $previous = $root = new Node();
        $emptyLines = [];
        try { //var_dump($source);
            $gen = function () use($source) {
                foreach ($source as $key => $value) {
                    yield ++$key => $value;
                }
            };
            foreach ($gen() as $lineNb => $lineString) {
                $n = new Node($lineString, $lineNb);
                $deepest = $previous->getDeepestNode();
                if ($n->type & (Y::LITTERALS|Y::BLANK|Y::COMMENT|Y::TAG) || $deepest->type & Y::PARTIAL) {
                    if ($this->onSpecialType($n, $previous, $emptyLines, $lineString)) continue;
                }
                //Note: 6.6 comments: Note that outside scalar content, a line containing only white space characters is taken to be a comment line.
                    foreach ($emptyLines as $blankNode) {
                        if ($blankNode !== $previous) {
                            $blankNode->getParent()->add($blankNode);
                        }
                    }
                $emptyLines = [];
                switch ($n->indent <=> $previous->indent) {
                    case -1: $target = $previous->getParent($n->indent);
                        if ($n->type & Y::ITEM) {
                            $target = $previous->getParent($n->indent, Y::KEY);
                        }
                        break;
                    case 0:
                        if ($n->type & Y::KEY && $n->indent === 0) {
                            $target = $root;
                        } elseif($n->type & Y::ITEM && $deepest->type & Y::KEY && is_null($deepest->value)) {
                            $target = $deepest;
                        } else {
                            $target = $previous->getParent();
                        }
                        break;
                    default:
                        $target = $previous;
                        if ($previous->type & Y::ITEM) {
                            if ($deepest->type & (Y::KEY|Y::TAG) && is_null($deepest->value)) {
                                $target = $deepest;
                            }
                        }
                }
                if ($this->onContextType($n, $target, $lineString)) continue;
                $target->add($n);
                $previous = $n;
            }
            if ($this->debug === 2) {
                print_r((new \ReflectionClass(Y::class))->getConstants());
                echo "\033[33mParsed Structure\033[0m\n";
                print_r($root);
                exit();
            }
            $out = Builder::buildContent($root, $this->debug);
            return $out;
        } catch (\Error|\Exception|\ParseError $e) {
            $file = $this->filePath ? basename($this->filePath) : 'YAML STRING';
            $message = basename($e->getFile())."@".$e->getLine().": ".$e->getMessage()." for '$file' @".($lineNb)."\n";
            if ($this->options & self::NO_PARSING_EXCEPTIONS) {
                // trigger_error($message, E_USER_WARNING);
                self::$error = $message;
                return null;
            }
            $this->debug && print_r($root);
            throw new \Exception($message, 3);
        }
    }

    private function onSpecialType(Node &$n, Node &$previous, &$emptyLines, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest->type & Y::PARTIAL) {
            $add = trim($lineString) === '' ? "\n" : trim($lineString);
            if ($add !== "\n" && $deepest->value[-1] !== "\n") {
                $add = ' '.$add;
            }
            $deepest->parse($deepest->value.$add);
            return true;
        }
        if ($n->type & Y::BLANK) {
            if ($previous->type & Y::SCALAR) $emptyLines[] = $n->setParent($previous->getParent());
            if ($deepest->type & Y::LITTERALS) $emptyLines[] = $n->setParent($deepest);
            return true;
        }
        //comment is fullline : forces 'root' as parent IF NOT inside a LITTERAL
        if ($n->type & Y::COMMENT &&
            !($previous->getParent()->value->type & Y::LITTERALS) &&
            !($deepest->type & Y::LITTERALS)) {
                $previous->getParent(0)->add($n);
                return true;
        }
        if ($n->type & Y::TAG && is_null($n->value) && $previous->type & (Y::ROOT|Y::DOC_START|Y::DOC_END)) {
            $n->value = '';
        }
        return false;
    }

    private function onContextType(Node &$n, Node &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if (($previous->type & Y::LITTERALS && $n->indent >= $previous->indent) || (($deepest->type & Y::LITTERALS) && is_null($deepest->value))) {
            $n->type = Y::SCALAR;
            $n->identifier = null;
            $n->value = trim($lineString);
            $previous = $deepest->getParent();
            return false;
        }
        if (is_null($deepest->value) && $deepest->type & (Y::LITTERALS|Y::REF_DEF|Y::SET_VALUE|Y::TAG)) {
            $previous = $deepest;
        }
        if ($n->type & Y::SCALAR && $previous->type & Y::SCALAR) {
            $previous = $previous->getParent();
        }
        return false;
    }
}
