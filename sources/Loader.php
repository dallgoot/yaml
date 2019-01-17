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
    public const IGNORE_DIRECTIVES = 1;//DONT include_directive
    public const IGNORE_COMMENTS    = 2;//DONT include_comments
    public const NO_PARSING_EXCEPTIONS = 4;//DONT throw Exception on parsing errors
    public const NO_OBJECT_FOR_DATE = 8;//DONT import date strings as dateTime Object

    //privates
    /* @var null|false|array */
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

    /**
     * Loader constructor
     *
     * @param string|null       $absolutePath The absolute file path
     * @param int|null          $options      The options (bitmask as int value)
     * @param integer|bool|null $debug        The debug level as either boolean (true=1) or any integer
     */
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
     * Gets the source iterator.
     *
     * @param string|null $strContent  The string content
     *
     * @throws \Exception if self::content is empty or splitting on linefeed has failed
     * @return Generator  The source iterator.
     */
    private function getSourceIterator($strContent = null)
    {
        $source = $this->content ?? preg_split("/\n/m", preg_replace('/(\r\n|\r)/', "\n", $strContent), 0, PREG_SPLIT_DELIM_CAPTURE);
        //TODO : be more permissive on $strContent values
        if (!is_array($source) || !count($source)) throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        return function () use($source) {
            foreach ($source as $key => $value) {
                yield ++$key => $value;
            }
        };
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
        $sourceIterator = $this->getSourceIterator($strContent);
        $root = new Node();
        $root->value = new NodeList;
        $previous = $root;
        $emptyLines = [];
        try {
            foreach ($sourceIterator() as $lineNb => $lineString) {
                $n = new Node($lineString, $lineNb);//var_dump($n);
                if ($this->onSpecialType($n, $previous, $emptyLines, $lineString)) continue;
                $this->attachBlankLines($emptyLines, $previous);
                $emptyLines = [];
                $target = $previous;
                switch ($n->indent <=> $previous->indent) {
                    case -1:
                        $target = $previous->getParent($n->indent);break;//, $n->type & Y::ITEM ? Y::KEY : null);
                    case 0:
                        $this->onEqualIndent($n, $previous, $target);break;
                    default:
                        $this->onMoreIndent($previous, $target);
                }
                if ($this->onContextType($n, $target, $lineString)) continue;
                $target->add($n);
                $previous = $n;
            }
            if ($this->debug === 2) {
                print_r((new \ReflectionClass(Y::class))->getConstants());
                print_r($root);
            } else {
                $out = Builder::buildContent($root, $this->debug);
                return $out;
            }
        } catch (\Error|\Exception|\ParseError $e) {
            $file = $this->filePath ? realpath($this->filePath) : '#YAML STRING#';
            $message = $e->getMessage()."\n ".$e->getFile().":".$e->getLine();
            if ($this->options & self::NO_PARSING_EXCEPTIONS) {
                // trigger_error($message, E_USER_WARNING);
                self::$error = $message;
                return null;
            }
            !is_null($this->debug) && print_r($root);
            throw new \Exception($message." for $file:$lineNb", 1, $e);
        }
    }

    /**
     * Modify parent target when current Node indentation is superior to previous node indentation
     *
     * @param Node $target   The target
     * @param Node $previous The previous
     *
     * @return null
     */
    public function onMoreIndent(Node &$previous, Node &$target)
    {
        // switch ($previous->type) {
        //     case 'value':
        //         # code...
        //         break;

        //     default:
        //         # code...
        //         break;
        // }
        // var_dump(__METHOD__);
        if ($previous->type & Y::ITEM) {
            $deepest = $previous->getDeepestNode();
            if ($deepest->type & (Y::KEY|Y::TAG) && is_null($deepest->value)) {
                $target = $deepest;
            }
        }
        // if ($previous->type & Y::LITTERALS) {
        //     $n->type = Y::SCALAR;
        //     $n->identifier = null;
        //     // $n->value = ltrim($lineString);
        //     // $previous = $previous;
        //     var_dump('NEvEr HERE3');//die();
        // }
        // $target = $previous;
    }

    //TODO: rearrange conditions call

    /**
     * Modify parent target when current Node indentation is equal to previous node indentation
     *
     * @param Node $n        The current Node
     * @param Node $previous The previous
     * @param Node $target   The target
     *
     * @return null
     */
    private function onEqualIndent(Node &$n, Node &$previous, Node &$target)
    {
        $deepest = $previous->getDeepestNode();
        if ($n->type & Y::KEY && $n->indent === 0) {
            $target = $previous->getParent(-1);//get root
        } elseif ($n->type & Y::ITEM && $deepest->type & Y::KEY && is_null($deepest->value)) {
            $target = $deepest;
        } else {
            $target = $previous->getParent();
        }
    }

    /**
     * Attach blank(empty) Nodes savec in $emptylines to their parent (it means they are needed)
     *
     * @param array $emptyLines The empty lines
     * @param Node  $previous   The previous
     *
     * @return null
     */
    public function attachBlankLines(array &$emptyLines, Node &$previous)
    {
        foreach ($emptyLines as $blankNode) {
            if ($blankNode !== $previous) {
                $blankNode->getParent()->add($blankNode);
            }
        }
    }

    /**
     * For certain (special) Nodes types some actions are required BEFORE parent assignment
     *
     * @param Node   $n          The current Node being treated in self::parse
     * @param Node   $previous   The previous Node
     * @param array  $emptyLines The empty lines
     * @param string $lineString The current line string (= raw value of the current Node)
     *
     * @return boolean  if True self::parse skips changing previous and adding to parent
     * @see self::parse
     */
    private function onSpecialType(Node &$n, Node &$previous, array &$emptyLines, string $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest->type & Y::PARTIAL) {
            $add = empty($lineString) ? "\n" : ltrim($lineString);
            if ($add !== "\n" && $deepest->value[-1] !== "\n") {
                $add = ' '.$add;
            }
            $deepest->parse($deepest->value.$add);
            return true;
        } else {
            switch ($n->type) {
                // case Y::LITT_FOLDED: die('found LIterral alone!!');
                case Y::BLANK:
                    // $this->onSpecialBlank($emptyLines, $n, $previous, $deepest);
                    if ($previous->type & Y::SCALAR)   $emptyLines[] = $n->setParent($previous->getParent());
                    if ($deepest->type & Y::LITTERALS) $emptyLines[] = $n->setParent($deepest);
                    return true;
                case Y::COMMENT:
                    if (!($previous->getParent()->value->type & Y::LITTERALS) && !($deepest->type & Y::LITTERALS)) {
                        $previous->getParent(0)->add($n);
                        return true;
                    };
                    break;
                case Y::TAG: if (is_null($n->value)) {
                                $n->value = '';
                             }
                default: return false;
            }
        }
        return false;
    }

    // private function onSpecialBlank(array &$emptyLines, Node $n, Node $previous, Node $deepest)
    // {
    //     if ($previous->type & Y::SCALAR)   $emptyLines[] = $n->setParent($previous->getParent());
    //     if ($deepest->type & Y::LITTERALS) $emptyLines[] = $n->setParent($deepest);
    // }

    /**
     * According to the current Node type and deepest value
     * this indicates if self::parse skips (or not) the parent and previous assignment
     *
     * @param      Node     $n           The current Node
     * @param      Node     $previous    The previous Node
     * @param      <type>   $lineString  The current Node raw line string
     *
     * @return     boolean  True if context, False otherwiser
     * @todo  is this really necessary according ot other checkings out there ?
     */
    private function onContextType(Node &$n, Node &$previous, $lineString):bool
    {
        $deepest = $previous->getDeepestNode();
        if (($deepest->type & Y::LITTERALS) && is_null($deepest->value)) {
            $n->type = Y::SCALAR;
            $n->identifier = null;
            $n->value = ltrim($lineString);
            $previous = $deepest;//->getParent();
            // var_dump('NEvEr HERE2');//die();
            return false;
        }
        /*var_dump('HERE',
            $n->indent >= $previous->indent,
            Y::LITT,
            Y::LITT_FOLDED,
            Y::LITT+Y::LITT_FOLDED,
            $n,
            // $deepest,
            // decbin(Y::LITTERALS),
            // decbin(Y::LITT+Y::LITT_FOLDED),
            // decbin($previous->type),
            // decbin($deepest->type),
            is_null($deepest->value));*/
        if (is_null($deepest->value) && $deepest->type & (Y::LITTERALS|Y::REF_DEF|Y::SET_VALUE|Y::TAG)) {
            $previous = $deepest;//var_dump($deepest);die();
        }
        // if ($n->type & Y::SCALAR && $previous->type & Y::SCALAR) {
        //     $previous = $previous->getParent();
        // }
        return false;
    }
}
