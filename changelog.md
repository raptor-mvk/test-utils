#Changelog

All notable changes to this project will be documented in this file.

## [1.5.1](https://github.com/raptor-mvk/test-utils/compare/v1.5.0-dev...v1.5.1) - 2019-09-20
### Changed in 1.5.1
- Mark previous versions as dev, 1.5.1 is the first stable version

## [1.5.0](https://github.com/raptor-mvk/test-utils/compare/v1.4.0-dev...v1.5.0-dev) - 2019-09-17
### Changed in 1.5.0
- Change symfony/console version to ^3.0|^4.0 for better compatibility

## [1.4.0](https://github.com/raptor-mvk/test-utils/compare/v1.3.0-dev...v1.4.0-dev) - 2019-08-31
### Changed in 1.4.0
- Symfony code style applied instead of PSR-12
  - All interfaces have  `Interface` suffix
  - All traits have `Trait` suffix

## [1.3.0](https://github.com/raptor-mvk/test-utils/compare/v1.2.6-dev...v1.3.0-dev) - 2019-07-30
### Added in 1.3.0
- Following methods are added to trait `ExtraUtils`:
  - `expectExceptionExactMessage(string $message)`

### Removed in 1.3.0
- Following methods are removed from trait `WithVFS`:
  - `getEscapedFullPath(string $path)`

## [1.2.6](https://github.com/raptor-mvk/test-utils/compare/v1.2.5-dev...v1.2.6-dev) - 2019-07-15
### Added in 1.2.6
- Following methods are added to trait `ExtraAssertions`:
  - `assertReturnsCarbonNowWithoutMicroseconds(callable $func, ?string $message = null)`

## [1.2.5](https://github.com/raptor-mvk/test-utils/compare/v1.2.4-dev...v1.2.5-dev) - 2019-07-12
### Added in 1.2.5
- Following methods are added to trait `ExtraAssertions`:
  - `assertStringsAreSameIgnoringEOL(string $expected, string $actual, ?string $message = null)`

## [1.2.4](https://github.com/raptor-mvk/test-utils/compare/v1.2.3-dev...v1.2.4-dev) - 2019-07-10
### Changed in 1.2.4

- fix error in `GeneratorDataProcessor`

## [1.2.3](https://github.com/raptor-mvk/test-utils/compare/v1.2.2-dev...v1.2.3-dev) - 2019-07-09
### Added in 1.2.3
- Following methods are added to trait `ExtraAssertions`:
  - `assertReturnsCarbonNow(callable $func, ?string $message = null)`

### Changed in 1.2.3
- method `getLastErrors` is added to `Generator` interface
- error messages are added to `GenerateIDETestContainerCommand`

## [1.2.2](https://github.com/raptor-mvk/test-utils/compare/v1.2.1-dev...v1.2.2-dev) - 2019-07-08
### Changed in 1.2.2
- fix error in `WrapperDataProcessor`

## [1.2.1](https://github.com/raptor-mvk/test-utils/compare/v1.2.0-dev...v1.2.1-dev) - 2019-07-08
### Changed in 1.2.1
- fix errors in `generate-ide-test-containers`

## [1.2.0](https://github.com/raptor-mvk/test-utils/compare/v1.1.0-dev...v1.2.0-dev) - 2019-07-07
### Changed in 1.2.0
- fix errors in `_ide_test_container.php`
- `DataLoaderFactory` and `DirectoryDataLoaderFactory` are removed

## [1.1.0](https://github.com/raptor-mvk/test-utils/compare/v1.0.0-dev...v1.1.0-dev) - 2019-07-07
### Changed in 1.1.0
-   `mikey179/vfsstream` is moved from require-dev to require section of
    composer.json

-   trait `ExtraAssertions` does not include `ExtraUtils` anymore

-   `DataLoader` and `DirectoryDataLoader` are now interfaces

-   all classes are made final, for which this was possible

## [1.0.0](https://github.com/raptor-mvk/test-utils/releases/tag/v1.0.0-dev) - 2019-06-27
### Added in 1.0.0
-   trait `ExtraUtils` that contains following service methods:

  - `invokeMethod(object $object, string $methodName, ?array $parameters = null)`

-   trait `ExtraAssertions` that contains following assertions:

  - `assertArraysAreSame(array $expected, array $actual, ?string $message = null)`

  - `assertArraysAreSameIgnoringOrder(array $expected, array $actual, ?string $message = null)`

  - `assertArraysAreSameIgnoringOrderRecursive(array $expected, array $actual, ?string $message = null)`

-   trait `WithVFS` that provides adapted interface for `mikey179/vfsstream`
    with following methods:

  - `addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null)`

  - `addDirectoryToVFS(string $dirname, ?int $permissions = null)`

  - `addStructure(array $structure)`

  - `getFullPath(string $path)`

  - `getEscapedFullPath(string $path)`

-   trait `WithDataLoader` that provides method `loadDataFromFile(string
    $filename)` for easy use `DataLoader` in data providers

-   command `generate-ide-test-containers` that generate service file for IDE
    used to autocomplete