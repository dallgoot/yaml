<?php

namespace Dallgoot\Yaml;

/**
 * Encapsulate the properties of a YAML Document
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class YamlProperties
{
    /** @var null|boolean */
    public $_hasDocStart; // null = no docstart, true = docstart before document comments, false = docstart after document comments
    /** @var array */
    public $_anchors  = [];
    /** @var array */
    public $_comments = [];
    /** @var array */
    public $_tags     = [];
    /** @var int */
    public $_options;
    /** @var null|string */
    public $value;

    /**
     * Creates API object to be used for the document provided as argument
     *
     * @param YamlObject $obj the YamlObject as the target for all methods call that needs it
     */
    public function __construct(int $buildingOptions)
    {
        $this->_options = $buildingOptions;
    }

}
