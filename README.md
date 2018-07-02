# yamloader WORK IN PROGRESS !!! [![Build Status](https://travis-ci.org/dallgoot/yaml.svg?branch=master)](https://travis-ci.org/dallgoot/yaml) [![Maintainability](https://api.codeclimate.com/v1/badges/dfae4b8e665a1d728e3d/maintainability)](https://codeclimate.com/github/dallgoot/yaml/maintainability)
PHP library to load and parse YAML file to PHP datatypes equivalent

Support:
- YAML specifications version 1.2 http://yaml.org/spec/1.2/spec.html
- multiple document in a file
- directives
- references (option : enabled by default)
- comments (option : enabled by default)
- tags
- multi-line quoted (or not) values
- complex mapping
- JSON values
- short syntax for mapping and sequences

Features:
- recover from some parsing errors
- tolerance to tabulations
- rename key names that are not valid PHP property name (option : disabled by default)

Return a YAMLOBJECT


Performances:

	memory usage:


Thanks to https://www.json2yaml.com/convert-yaml-to-json