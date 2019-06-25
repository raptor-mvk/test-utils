Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
### Added
- trait `ExtraUtils` that contains following service methods:
  - `invokeMethod(object $object, string $methodName, ?array $parameters = null)`
- trait `ExtraAssertions` that contains following assertions:
  - `assertArraysAreSame(array $expected, array $actual, ?string $message = null)`
  - `assertArraysAreSameIgnoringOrder(array $expected, array $actual, ?string $message = null)`
  - `assertArraysAreSameIgnoringOrderRecursive(array $expected, array $actual, ?string $message = null)`
  - `assertStringIsSameAsFile(string $expected, string $actual, ?string $message = null)`
- trait `WithVFS` that provides adapted interface for `mikey179/vfsstream` with following methods:
  - `addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null)`
  - `addDirectoryToVFS(string $dirname, ?int $permissions = null)`
  - `addStructure(array $structure)`
  - `getFullPath(string $path)`
  - `getEscapedFullPath(string $path)`
- test data loader from JSON files that wraps each test case into test data container
- command `generate-ide-test-containers` that generate service file for IDE used to autocomplete


