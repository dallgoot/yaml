# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.9.0.0

### Changed

- minimum PHP version supported is now 8.1.14
- autoloading for PSR-4 (sorry to have polluted autoloads files worldwide 😢)
- parsing in NodeFactory
- logic in Dallgoot\Yaml\Dumper

### Removed

- support for PHP before 8.1.14
- some dev dependencies
- Dallgoot\Yaml class replaced by Dallgoot\Yaml\Yaml

### Fixed

- PHP Notice reported by <https://github.com/stephanedupont> in <https://github.com/dallgoot/yaml/issues/9>
- dumping bug in Dallgoot\Yaml\DumperHandlers reported by <https://github.com/albosmart-ro> in <https://github.com/dallgoot/yaml/issues/7>>

## 0.3.2.0

this version is not stable enough, nor finished, nor fully tested to be used in production, please prefer the 1.0.0
