# Dallgoot : YAML library for PHP - Beta !!!

[![Build Status](https://travis-ci.org/dallgoot/yaml.svg?branch=master)](https://travis-ci.org/dallgoot/yaml) [![PHP from Packagist](https://img.shields.io/packagist/php-v/dallgoot/yaml)](https://packagist.org/packages/dallgoot/yaml) [![Packagist](https://img.shields.io/packagist/dt/dallgoot/yaml)](https://packagist.org/packages/dallgoot/yaml)
[![Maintainability](https://api.codeclimate.com/v1/badges/dfae4b8e665a1d728e3d/maintainability)](https://codeclimate.com/github/dallgoot/yaml/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master)

PHP library to load and parse YAML file to coherent PHP datatypes equivalent

![Dallgoot/Yaml Library](dallgoot_yaml.png)

## Installation

- Dependencies are only useful for building documentation or for code contribution, so the "--update-no-dev" prevent from downloading and managing packages that you probably won't use.

You first need [Composer](https://getcomposer.org/) and PHP ^7.1.10

```bash
composer require --update-no-dev dallgoot/yaml
```

## Usage

See examples files in [examples folder](./examples)

## Features:

- *consistent* PHP datatypes :
  - object for mappings
  - array for sequences
  - common scalars : string, integer, float, INF, NAN
  - JSON, DateTime(option), etc.
- specific types (objects)
  - **YamlObject** for each Yaml content (inherits from PHP class ArrayIterator)
  - **Compact** for compact/short YAML syntax (inherits from PHP class ArrayIterator)
  - **Tagged** object when tag is not determinable
- recover from some parsing errors
- tolerant to tabulations
- debug levels :
  - 1 : print each line Node Type class and exit
  - 2 : print Loader global map structure and exit
  - 3 : print each document NodeList and exit

## Support:

- YAML specifications [version 1.2](http://yaml.org/spec/1.2/spec.html)
- multi-line values (simple|double quoted or not, compact mapping|sequence or JSON)
- multiple documents in a content (file or string)
- compact syntax for mappings and sequences
- comments (option : enabled by default)
- references (option : enabled by default)
- tags with behaviour customization (overriding for common(CoreSchema), or specifying for custom) via implementing Tag/SchemaInterface.

## What's different from other PHP Yaml libraries ?

|                                                                      | YAML version supported | coherent data types | multiple documents | JSON format validation | complex mapping | real reference behaviour | custom tags handling |
| -------------------------------------------------------------------- |:----------------------:|:-------------------:|:------------------:|:----------------------:|:---------------:|:------------------------:|:--------------------:|
| [Symfony Yaml](https://symfony.com/doc/current/components/yaml.html) | 1.2                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [php-yaml](https://pecl.php.net/package/yaml)                        | 1.1                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [syck](http://pecl.php.net/package/syck)                             | 1.0                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| [spyc](https://github.com/mustangostang/spyc)                        | 1.0                    | ❌                   | ❌                  | ❌                      | ❌               | ❌                        | ❌                    |
| **Dallgoot/Yaml**                                                    | 1.2                    | ✔️                  | ✔️                 | ✔️                     | ✔️              | ✔️                       | ✔️                   |

- coherent data types (see [coherence.md](./documentation/coherence.md) for explanations)
- JSON format validation (Option, Note: if valid as per PHP function *json_encode*)
- complex mapping (Note: keys are JSON formatted strings)
- real reference behaviour : changing reference value modify other reference calls

## Contributing

  Only contributions concerning bug fixes will be review ATM.
  Requests for features will be dealt with after reading/writing YAML is considered bug free (and al current Options are implemented)

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
- TAG : function for 'php/object' that provides the correct namespace to build
- NEON compatibility???

## Performances

    TBD

## Thanks

- (https://yaml.org)
- (https://www.json2yaml.com/convert-yaml-to-json)
- [Symfony Yaml](https://symfony.com/doc/current/components/yaml.html)
