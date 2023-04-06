# Sometimes the handling of dataypes in other libraries can be confusing

## Example from Symfony Yaml
```php
$object = new \stdClass();
$object->foo = 'bar';

$dumped = Yaml::dump(['data' => $object], 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
// $dumped = "data:\n    foo: bar"
```

## Dallgoot\Yaml removes this ambiguity by matching a specific PHP data types with its YAML counterpart like so :
- PHP standard objects -> YAML mapping
- PHP standard array -> YAML sequence
- _Dallgoot\Types\Compact_ -> YAML mapping or sequence according to content
- _Dallgoot\Types\YamlObject_ -> YAML mapping or sequence according to content