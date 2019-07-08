# Raptor Test Utils v1.2.1

(c) Mikhail Kamorin aka raptor_MVK

## Overview

Package contains following components:
- trait `ExtraUtils` that contains set of service methods used to make testing easier
- trait `ExtraAssertions` that contains set of additional assertions
- trait `WithVFS` that provides adapted interface for `mikey179/vfsstream` (virtual file system)
- test data loader from JSON files that wraps each test case into test data container
- command `generate-ide-test-containers` that generate service file for IDE used to autocomplete

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
php composer.phar require "raptor/test-utils:1.2.*"
```

## Usage

### Additional service methods

Add trait `ExtraUtils` to the class that contains tests or to the common base test class. After
that the following static methods will be available:

 - `invokeMethod(object $object, string $methodName, ?array $parameters = null)` invokes protected or private method
   with the given parameters

### Additional assertions

Add trait `ExtraAssertions` to the class that contains tests or to the common base test class. After that the following
additional assertions will be available:

 - `assertArraysAreSame(array $expected, array $actual, ?string $message = null)` checks the assertion that two arrays
   are same (order of elements, their types coincides at every level). Before checking, arrays are encoded as JSON
   strings, therefore you cannot use objects or functions as elements of an array.
 - `assertArraysAreSameIgnoringOrder(array $expected, array $actual, ?string $message = null)` checks the assertion that
   two associative arrays contains same elements (at every level) ignoring their order at the top level. Before
   checking, arrays are encoded as JSON strings, therefore you cannot use objects or functions as elements of an array.
 - `assertArraysAreSameIgnoringOrderRecursive(array $expected, array $actual, ?string $message = null)` checks the
   assertion that two associative arrays contains same elements ignoring their order at every level. Before checking,
   arrays are encoded as JSON strings, therefore you cannot use objects or functions as elements of an array.

### Virtual file system

Add trait `ExtraUtils` or `ExtraAssertions` to the class that contains tests or to the common base test class. Method
`setupVFS` should be used in `setUp` method or in the test method just before using other methods of the trait.
No tearDown actions is needed regarding virtual file system. After that the following additional methods will be available:

 - `addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null)` adds file with given permissions
   and content to virtual file system.
 - `addDirectoryToVFS(string $dirname, ?int $permissions = null)` adds directory with given permissions to virtual file
   system
 - `addStructure(array $structure)` adds directory structure to virtual file system. Structure is represented as a tree,
   where leaves are files with key as the file name and value as the file content
 - `getFullPath(string $path)` returns full path to the file that is used outside virtual file system
 - `getEscapedFullPath(string $path)` returns full path to the file with escaped slashes that is used in regular
   expressions

### Test data loader

Data loader allows you to extract test data from data provider into separate JSON file. As a result of data loading a
set of test cases is formed, where data for each test case is wrapped into container object. Values of specific fields
from file are returned by getters. Such an approach allows you to solve the following tasks:

 - extract test data from code to separate JSON files
 - pass into testing method many parameters without inflating method signature
 - organize test data into hierarchical structure, when there is common data in several test cases

Requirements to JSON file:
 - file should contain **array** of JSON objects, array may contain single object
 - field names should not start with underscore except cases specifically noted below
 - field names should contain only lowercase letters, digits and underscore
 - each object of the array should belong to one of two types:
     1. Test case. Such objects **should not** contain service field `_children` and **should** contain
        service field `_name`
     2. Array of test cases with default values for some fields. Such objects **should** contain service field
        `_children` and **should not** contain service field `_name`
 - service field `_name` should contain string, it is name of the test case
 - values of service field `_name` should be unique and non-empty
 - service field `_children` should contain array, that meets the same requirements as the root array of the file

Objects of first type are processed by following algorithm:
 - if object being processed has parent object of second type, then:
     - if parent object contains fields, that does not belong to the object being processed, then they are added t
      the object being processed with appropriate values
 - if parent object has parent object of second type too, then procedure is repeated for its parent and so on     
 
Intermediate result of data loader is an array of all objects of first type with values of service field `_name` as
keys. Service field `_name` itself is excluded from the objects.

Then each array value is wrapped into TestDataContainer object. Values are retrieved from containers using getters. 
Name of getters follow standard agreements:
 - if the name of field starts with 'is', then the getter name is field name converted to camel case
 - otherwise, if the field is of bool type, then the getter name is field name converted to studly case and prefixed with
   'is'
 - otherwise, the getter name is field name converted to studly case and prefixed with 'get'

### Generator of service file for IDE

Service file for IDE is used for auto-completion when using container objects. Generator is called by following command:

```bash
    php vendor/raptor/test-utils/generate-ide-test-containers path
```

where `path` - path to directory with JSON files that contain test data. Directory is processed recursively.
Requirements to JSON files:
 - the name of each JSON file without an extension is converted to studly case, after that conversion all strings must
   be different. Duplicate names will not be processed
 - each JSON file should meet the requirements from the section **[Test data loader](#test-data-loader)**

As a result of the command execution, the _ide_test_containers.php file is generated in the project root. This file
contains container class for each JSON file in the specified directory. Each container class is located in the root
namespace and has the name obtained by converting the name of the JSON file to studly case and adding the suffix
`DataContainer`. You can use auto-completion after adding a PHPDoc comment `@var` with appropriate class and variable.

### Data loader usage example with trait WithDataLoader

```php
    use Raptor\Test\TestDataContainer\TestDataContainer;

    class someTests extends TestCase
    {
        use WithDataLoader;
             
        ...
        
        /**
         * @dataProvider someDataProvider
         */
        public function testSomething(TestDataContainer $dataContainer): void
        {
            /** @var \SomeTestsDataContainer $dataContainer */
            $value = $dataContainer->getValue();
            ...
        }
        
        public function someDataProvider(): array
        {
            return $this->loadFromFile('some_tests.json'); 
        }
        
        ...
    }
```

## Authors

- Mikhail Kamorin aka raptor_MVK
- Igor Vodka