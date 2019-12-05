<?php
declare(strict_types=1);

namespace Dallgoot\Yaml;

use Dallgoot\Yaml\Nodes;

/**
 * Process reading a Yaml Content (loading file if required)
 * and for each line affects appropriate NodeType
 * and attach to proper parent Node
 * ie. constructs a tree of Nodes with a NodeRoot as first Node
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
final class Loader
{
    //public
    /* @var null|string */
    public static $error;
    public const IGNORE_DIRECTIVES     = 0b0001;//DONT include_directive
    public const IGNORE_COMMENTS       = 0b0010;//DONT include_comments
    public const NO_PARSING_EXCEPTIONS = 0b0100;//DONT throw Exception on parsing errors
    public const NO_OBJECT_FOR_DATE    = 0b1000;//DONT import date strings as dateTime Object

    //private
    /* @var null|array */
    private $content;
    /* @var null|string */
    private $filePath;
    /* @var integer */
    private $_debug = 0;
    /* @var integer */
    private $_options = 0;
    /* @var array */
    private $_blankBuffer = [];

    //Exceptions messages
    private const INVALID_VALUE        = self::class.": at line %d";
    private const EXCEPTION_NO_FILE    = self::class.": file '%s' does not exists (or path is incorrect?)";
    private const EXCEPTION_READ_ERROR = self::class.": file '%s' failed to be loaded (permission denied ?)";
    private const EXCEPTION_LINE_SPLIT = self::class.": content is not a string (maybe a file error?)";

    /**
     * Loader constructor
     *
     * @param string|null       $absolutePath The file absolute path
     * @param int|null          $options      The options (bitmask as int value)
     * @param integer|bool|null $debug        The debug level as either boolean (true=1) or any integer
     */
    public function __construct($absolutePath = null, $options = null, $debug = 0)
    {
        $this->_debug   = is_null($debug) ? 0 : min($debug, 3);
        $this->_options = is_int($options) ? $options : $this->_options;
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
        $adle_setting = "auto_detect_line_endings";
        ini_set($adle_setting, "true");
        $content = @file($absolutePath, FILE_IGNORE_NEW_LINES);
        ini_restore($adle_setting);
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
     * @return \Generator  The source iterator.
     */
    private function getSourceGenerator($strContent = null):\Generator
    {
        if (is_null($strContent) && is_null($this->content)) {
            throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        }
        if (!is_null($this->content)) {
            $source = $this->content;
        } else {
            $simplerLineFeeds = preg_replace('/(\r\n|\r)$/', "\n", (string) $strContent);
            $source = preg_split("/\n/m", $simplerLineFeeds, 0, \PREG_SPLIT_DELIM_CAPTURE);
        }
        if (!is_array($source) || !count($source)) {
            throw new \Exception(self::EXCEPTION_LINE_SPLIT);
        }
        foreach ($source as $key => $value) {
            yield ++$key => $value;
        }
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
        $generator = $this->getSourceGenerator($strContent);
        $previous = $root = new Nodes\Root();
        try {
            foreach ($generator as $lineNB => $lineString) {
                $node = NodeFactory::get($lineString, $lineNB);
                if ($this->_debug === 1) echo $lineNB.":".get_class($node)."\n";
                if ($this->needsSpecialProcess($node, $previous)) continue;
                $this->_attachBlankLines($previous);
                switch ($node->indent <=> $previous->indent) {
                    case -1: $target = $previous->getTargetOnLessIndent($node);
                        break;
                    case 0:  $target = $previous->getTargetOnEqualIndent($node);
                        break;
                    default: $target = $previous->getTargetOnMoreIndent($node);
                }
                $previous = $target->add($node);
            }
            $this->_attachBlankLines($previous);
            return $this->_debug === 1 ? null : (new Builder($this->_options, $this->_debug))->buildContent($root);
        } catch (\Throwable $e) {
            $this->onError($e, $lineNB);
        }
    }


    /**
     * Attach blank (empty) Nodes saved in $_blankBuffer to their parent (it means they are meaningful content)
     *
     * @param nodes\NodeGeneric  $previous   The previous Node
     *
     * @return null
     */
    private function _attachBlankLines(Nodes\NodeGeneric $previous)
    {
        foreach ($this->_blankBuffer as $blankNode) {
            if ($blankNode !== $previous) {
                $blankNode->getParent()->add($blankNode);
            }
        }
        $this->_blankBuffer = [];
    }

    /**
     * For certain (special) Nodes types some actions are required BEFORE parent assignment
     *
     * @param Nodes\NodeGeneric   $previous   The previous Node
     *
     * @return boolean  if True self::parse skips changing previous and adding to parent
     * @see self::parse
     */
    private function needsSpecialProcess(Nodes\NodeGeneric $current, Nodes\NodeGeneric $previous):bool
    {
        $deepest = $previous->getDeepestNode();
        if ($deepest instanceof Nodes\Partial) {
            return $deepest->specialProcess($current,  $this->_blankBuffer);
        } elseif(!($current instanceof Nodes\Partial)) {
            return $current->specialProcess($previous, $this->_blankBuffer);
        }
        return false;
    }

    // private function onError(\Throwable $e, \Generator $generator)
    private function onError(\Throwable $e, int $lineNB)
    {
        $file = $this->filePath ? realpath($this->filePath) : '#YAML STRING#';
        $message = $e->getMessage()."\n ".$e->getFile().":".$e->getLine();
        if ($this->_options & self::NO_PARSING_EXCEPTIONS) {
            self::$error = $message;
            return null;
        }
        throw new \Exception($message." for $file:".$lineNB, 1, $e);
    }
}
