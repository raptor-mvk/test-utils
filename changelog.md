#Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2019-07-07
### Changed
- `mikey179/vfsstream` is moved from require-dev to require section of composer.json
- trait `ExtraAssertions` does not include `ExtraUtils` anymore
- `DataLoader` and `DirectoryDataLoader` are now interfaces
- all classes are made final, for which this was possible


## [1.0.0] - 2019-06-27
### Added
- trait `ExtraUtils` that contains following service methods:
  - `invokeMethod(object $object, string $methodName, ?array $parameters = null)`
- trait `ExtraAssertions` that contains following assertions:
  - `assertArraysAreSame(array $expected, array $actual, ?string $message = null)`
  - `assertArraysAreSameIgnoringOrder(array $expected, array $actual, ?string $message = null)`
  - `assertArraysAreSameIgnoringOrderRecursive(array $expected, array $actual, ?string $message = null)`
- trait `WithVFS` that provides adapted interface for `mikey179/vfsstream` with following methods:
  - `addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null)`
  - `addDirectoryToVFS(string $dirname, ?int $permissions = null)`
  - `addStructure(array $structure)`
  - `getFullPath(string $path)`
  - `getEscapedFullPath(string $path)`
- trait `WithDataLoader` that provides method `loadDataFromFile(string $filename)` for easy use `DataLoader` in data
  providers
- command `generate-ide-test-containers` that generate service file for IDE used to autocomplete


[1.1.0]: https://github.com/raptor-mvk/test-utils/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/raptor-mvk/test-utils/releases/tag/v1.0.0