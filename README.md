# yamloader
#WORK IN PROGRESS !!! PHP library to load and parse YAML file to PHP datatypes equivalent

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
- rename key names that are not valid PHP property name (option : enabled by default)

Return a YAMLOBJECT with following API:
- getReference($referenceName = null) : get reference description by name or an array of all references 
- getComment($lineNumber = null) : get comment at line number or an array of all of them
- getDocument($identifier = null) : return document object by identifier (name or index starting @ 1) or array of documents 
- toString : returns string representation of the Yaml file content


Performances:

	memory usage:


Thanks to https://www.json2yaml.com/convert-yaml-to-json 