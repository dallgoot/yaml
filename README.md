# YAML Library for PHP - WORK IN PROGRESS !!!
[![Build Status](https://travis-ci.org/dallgoot/yaml.svg?branch=master)](https://travis-ci.org/dallgoot/yaml) [![Maintainability](https://api.codeclimate.com/v1/badges/dfae4b8e665a1d728e3d/maintainability)](https://codeclimate.com/github/dallgoot/yaml/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master)

PHP library to load and parse YAML file to PHP coherent datatypes equivalent

## Features:
- define *appropriate* PHP datatypes for values ie. object for mappings, array for sequences, JSON, DateTime, integers, floats, etc.
- recover from some parsing errors
- tolerance to tabulations

## Support:
- YAML specifications [version 1.2](http://yaml.org/spec/1.2/spec.html)
- comments (option : enabled by default)
- complex mapping (Note: keys are JSON encoded strings)
- JSON values (valid as per PHP function _json_encode_)
- compact syntax for mapping and sequences
- multi-line values (simple|double quoted or not, compact mapping|sequence or JSON)
- multiple documents in a file (Note: currently only on document start prefix)
- references (option : enabled by default)
- tags (Note: partial implementation as Dallgoot\Yaml\Tag object)

## What's different from other PHP Yaml libraries ?
- coherent data types (see [coherence.md](coherence.md) for explanations)
- support multiple documents in one YAML content (string or file)
- complex mapping

<!-- - Dallgoot\Yaml\Loader : Return an array of *YamlObject* for multiple document, or *YamlObject* for one document
- Dallgoot\Yaml\Dumper : create YAML structure according to data types provided :
    - a YamlObject is a document (with Comments, References, Directives)
    - an array of YamlObject is a multi-documents YAML file.
    - any other datatypes is a one YAML Document
- Dallgoot\Yaml\Tag : an object with properties _tagname_, _value_ -->

## TODO:
- implement/verify Loader::Options, Dumper::Options
- DUMPER:
    - finish implementation
    - set up tests
- DEFINE debug levels :
    - print Loader Tree structure
    - ???
- Code coverage : Units tests for each classes methods
- Documentation :
  - build classes docs
  - Examples of each function of the API
- Benchmarks against other libs

## IMPROVEMENT
- more precise errors identification in YAML content
- Unicode checking (?)
- tags: default handling for common tags, and user-customized process for custom ones
- Force renaming key names that are not valid PHP property name
- directives : currently ignored

## Performances
    TBD



## Thanks
https://www.json2yaml.com/convert-yaml-to-json