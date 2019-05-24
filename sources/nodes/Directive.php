<?php

namespace Dallgoot\Yaml\Nodes;

use Dallgoot\Yaml;
use Dallgoot\Yaml\Regex;
use Dallgoot\Yaml\TagFactory;

/**
 *
 * @author  Stéphane Rebai <stephane.rebai@gmail.com>
 * @license Apache 2.0
 * @link    https://github.com/dallgoot/yaml
 */
class Directive extends NodeGeneric
{
    private const ERROR_BUILDING = "Error : can not build Directive";
    private const WARNING_LOWER_VERSION  = "The declared version '%f' is obsolete, there may be features that are deprecated and therefore not handled, minimum supported is :".Yaml::VERSION_SUPPORT;
    private const WARNING_HIGHER_VERSION = "The declared version '%f' is not yet supported, minimum supported is :".Yaml::VERSION_SUPPORT;

    /**
     * Builds a Directive : update YamlObject if applicable.
     *
     * @param      object|array       $parent  The parent
     *
     * @throws     \ParseError  If Tag handle has been already set before.
     *
     * @return     null
     */
    public function build(&$parent = null)
    {
        if (preg_match(Regex::DIRECTIVE_TAG, $this->raw, $matches)) {
            try {
                $yamlObject = $this->getRoot()->getYamlObject();
                //Try registering the handle in TagFactory
                TagFactory::registerHandle($matches['handle'], $matches['prefix']);
                $yamlObject->addTag($matches['handle'], $matches['prefix']);
            } catch (\Throwable $e) {
                throw new \ParseError(self::ERROR_BUILDING, 1, $e);
            }
        }
        if (preg_match(Regex::DIRECTIVE_VERSION, $this->raw, $matches)) {
            $contentVersion = (float) $matches['version'];
            if ($contentVersion > Yaml::VERSION_SUPPORT) {
                trigger_error(sprintf(self::WARNING_HIGHER_VERSION, $contentVersion), E_USER_WARNING);
            }
            if ($contentVersion < Yaml::VERSION_SUPPORT) {
                trigger_error(sprintf(self::WARNING_LOWER_VERSION, $contentVersion), E_USER_WARNING);
            }
        }
        return null;
    }

    public function add(NodeGeneric $child):NodeGeneric
    {
        return $child;
    }
}