# Dallgoot : YAML Library for PHP - Beta !!!

[![Build Status](https://travis-ci.org/dallgoot/yaml.svg?branch=master)](https://travis-ci.org/dallgoot/yaml) [![Maintainability](https://api.codeclimate.com/v1/badges/dfae4b8e665a1d728e3d/maintainability)](https://codeclimate.com/github/dallgoot/yaml/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master)

PHP library to load and parse YAML file to coherent PHP datatypes equivalent

## Features:

- define *appropriate* PHP datatypes :
  - object for mappings
  - array for sequences
  - common scalars : string, integer, float, INF, NAN
  - JSON, DateTime, etc.
- define specific types (objects)
  - YamlObject for each Yaml content (inherits from PHP class ArrayIterator)
  - Compact for compact/short YAML syntax (inherits from PHP class ArrayIterator)
  - Tagged Object when tag is not determinable
- recover from some parsing errors
- tolerant to tabulations
- DEFINE debug levels :
  - 1 : print each line Node Type class and exit
  - 2 : print Loader global map structure and exit
  - 3 : print each document NodeList and exit

## Support:

- YAML specifications [version 1.2](http://yaml.org/spec/1.2/spec.html)
- multiple documents in a content (file or string)
- comments (option : enabled by default)
- compact syntax for mappings and sequences
- multi-line values (simple|double quoted or not, compact mapping|sequence or JSON)
- references (option : enabled by default)
- tags with behaviour customization (overriding for common(CoreSchema), or specifying for custom) via implementing tag/SchemaInterface.

## What's different from other PHP Yaml libraries ?

|                                                                      | YAML version supported | coherent data types | multiple documents | JSON format validation | complex mapping | real reference behaviour | custom tags handling |
| -------------------------------------------------------------------- |:----------------------:|:-------------------:|:------------------:|:----------------------:|:---------------:|:------------------------:|:--------------------:|
| [Symfony Yaml](https://symfony.com/doc/current/components/yaml.html) | 1.2                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [php-yaml](https://pecl.php.net/package/yaml)                        | 1.1                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [syck](http://pecl.php.net/package/syck)                             | 1.0                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [spyc](https://github.com/mustangostang/spyc)                        | 1.?                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| **Dallgoot/Yaml**                                                    | 1.2                    | ✔️                  | ✔️                 | ✔️                     | ✔️              | ✔️                       | ✔️                   |

- coherent data types (see [coherence.md](./documentation/coherence.md) for explanations)
- support multiple documents in one YAML content (string or file)
- JSON format validation (if valid as per PHP function *json_encode*)
- complex mapping (Note: keys are JSON formatted strings)
- real reference behaviour : changing reference value modify other reference calls

## Before releasing

- Examples
  - double check references/anchors changes in YamlObject

- build classes docs
- verify gitattributes
- composer update + tests before release to Packagist

## Installation

```bash
composer require dallgoot/yaml
```

## ToDo
- Code coverage : target 100%
- Benchmarks against other libs


## Improvements
- Examples of each function of the API
- implement specific unit test for each YAML spec. invalid cases (what must not happen)
- DUMPER:
  - implement/verify Dumper::Options
- better/more precise errors identification (Yaml validation) with explanation in YAML content
- Unicode checking (???)
- OPTION : parse dates as PHP DateTime object
- OPTION: Force renaming key names that are not valid PHP property name
- docker-compose for easy testing
- TAG : function for 'php/object' that provides the correct namespace to build
- NEON compatibility???


## Performances

    TBD

## Thanks

(https://yaml.org)
(https://www.json2yaml.com/convert-yaml-to-json)
[Symfony Yaml](https://symfony.com/doc/current/components/yaml.html)
