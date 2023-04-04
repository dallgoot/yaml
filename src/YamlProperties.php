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
    public ?bool $_hasDocStart = null; // null = no docstart, true = docstart before document comments, false = docstart after document comments

    public array $_anchors  = [];

    public array $_comments = [];

    public array $_tags     = [];

    public int $_options;

    public ?string $value = null;

    /**
     * Creates API object to be used for the document provided as argument
     *
     * @param int $buildingOptions the YamlObject as the target for all methods call that needs it
     */
    public function __construct(int $buildingOptions)
    {
        $this->_options = $buildingOptions;
    }
}
