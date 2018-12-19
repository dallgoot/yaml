# YAML Library for PHP - WORK IN PROGRESS !!!
[![Build Status](https://travis-ci.org/dallgoot/yaml.svg?branch=master)](https://travis-ci.org/dallgoot/yaml) [![Maintainability](https://api.codeclimate.com/v1/badges/dfae4b8e665a1d728e3d/maintainability)](https://codeclimate.com/github/dallgoot/yaml/maintainability) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/dallgoot/yaml/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dallgoot/yaml/?branch=master)

PHP library to load and parse YAML file to PHP datatypes equivalent

## Features:
- define apropriate PHP datatypes for values ie. object for mappings, array for sequences, JSON, DateTime, integers, floats, etc.
- recover from some parsing errors
- tolerance to tabulations

## Support:
- YAML specifications version 1.2 http://yaml.org/spec/1.2/spec.html
- comments (option : enabled by default)
- complex mapping (Note: keys are JSON encoded strings)
- JSON values (valid as per PHP json_encode)
- compact syntax for mapping and sequences
- multi-line values (simple|double quoted or not, compact mapping|sequence or JSON)
- multiple document in a file (Note: only on document start ATM)
- references (option : enabled by default)
- tags (Note: partial implementation as Dallgoot\Yaml\Tag object)

## What's different from other PHP Yaml libraries
- support multiple documents in one YAML content (string or file)
- complex mapping
<!-- - coherent types support, other libraries don't provide types distinction between:
```yaml
---
sequence:
    - string_key: 1
#and
---
mapping:
    string_key: 1
```
For these libraries same type is returned :
```php
["sequence" => ["string_key"=> 1]]
["mapping"  => ["string_key"=> 1]]
```
In Dallgot\Yaml you get the following types:
```php
 object(Dallgoot\Yaml\YamlObject) {
    ["sequence"]=>
    array(1) {
      ["string_key"]=>
      int(1)
    }
}
//and
 object(Dallgoot\Yaml\YamlObject) {
    ["mapping"]=>
    object(stdClass) {
      ["string_key"]=>
      int(1)
    }
}
```
That is an issue when parsing YAML but also when dumping YAML content. -->
Take this example from Symfony/Yaml:
```php
$object = new \stdClass();
$object->foo = 'bar';

$dumped = Yaml::dump(array('data' => $object), 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
// $dumped = "data:\n    foo: bar"
```
The dumped result is wrong respecting to datatypes : object->mapping, array->sequence
So this should dump document as:
```yaml
- data:
    foo: bar
```
Note the "-" hyphen which makes possible to distinguish between a mapping key VS a sequence entry.
This distinction is crucial to allow respecting original YAML structure when content is loaded and dumped.

## API
  TBD
<!-- - Dallgoot\Yaml\Loader : Return an array of *YamlObject* for multiple document, or *YamlObject* for one document
- Dallgoot\Yaml\Dumper : create YAML structure according to data types provided :
    - a YamlObject is a document (with Comments, References, Directives)
    - an array of YamlObject is a multi-documents YAML file.
    - any other datatypes is a one YAML Document
- Dallgoot\Yaml\Tag : an object with properties _tagname_, _value_ -->

## TODO:
  TBD
<!-- - DUMPER: implementation
- DEFINE debug levels
- tags: default handling for common tags, and user-customized process for custom ones
- directives (to be implemented) : currently ignored
- IMPROVE : rename key names that are not valid PHP property name (to be implemented)
- IMPROVE : identifying errors in YAML content
- UNICODE checking (?)
 -->
## Performances
    TBD



## Thanks
https://www.json2yaml.com/convert-yaml-to-json