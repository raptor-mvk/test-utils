<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataLoader;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DataLoader;
use Raptor\TestUtils\DataLoader\DirectoryDataLoader;
use Raptor\TestUtils\DataLoader\DirectoryProcessedDataLoader;
use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\WithVFS;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class DirectoryProcessedDataLoaderTests extends TestCase
{
    use MockeryPHPUnitIntegration, ExtraAssertions, WithVFS;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupVFS();
    }

    /**
     * Checks that method _getDataProcessorClass_ returns correct class.
     */
    public function testGetDataProcessorClassReturnsCorrectClass(): void {
        $expectedClass = DataLoader::class;
        /** @var DataLoader|MockInterface $dataLoader */
        $dataLoader = Mockery::mock(DataLoader::class);
        $dataLoader->shouldReceive('getDataProcessorClass')->withNoArgs()->andReturn($expectedClass);
        $directoryDataLoader = new DirectoryProcessedDataLoader($dataLoader);

        $actualClass = $directoryDataLoader->getDataProcessorClass();

        static::assertSame($expectedClass, $actualClass);
    }

    /**
     * Checks that method _load_ throws _DataDirectoryNotFoundException_, if data directory was not found.
     */
    public function testLoadThrowsDataDirectoryNotFoundForNonExistingDir(): void
    {
        $dirname = 'nonexistent';
        $escapedPath = $this->getEscapedFullPath($dirname);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Root folder $escapedPath was not found\.$/");

        $path = $this->getFullPath($dirname);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Prepares directory data loader with mock object for data processor.
     *
     * @param callable|null $dataLoaderMockCallback function that takes mock object for data loader and performs its
     *                                              additional preparation
     * @return DirectoryDataLoader
     */
    private function prepareDirectoryDataLoader(?callable $dataLoaderMockCallback = null): DirectoryDataLoader
    {
        $dataLoaderMock = Mockery::mock(DataLoader::class);
        if ($dataLoaderMockCallback !== null) {
            $dataLoaderMockCallback($dataLoaderMock);
        }
        return new DirectoryProcessedDataLoader($dataLoaderMock);
    }

    /**
     * Checks that method _load_ throws _DataDirectoryNotFoundException_, if read access to data directory is denied.
     */
    public function testLoadThrowsDataDirectoryNotFoundForNonReadableDirectory(): void
    {
        $dirname = 'forbidden';
        $this->addDirectoryToVFS($dirname, 0);
        $escapedPath = $this->getEscapedFullPath($dirname);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Root folder $escapedPath was not found\.$/");

        $path = $this->getFullPath($dirname);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Checks that method _load_ throws _DataDirectoryNotFoundException_, if data directory is actually a file.
     */
    public function testLoadThrowsDataDirectoryNotFoundForFileInsteadOfDirectory(): void
    {
        $filename = 'accessible.json';
        $this->addFileToVFS($filename);
        $escapedPath = $this->getEscapedFullPath($filename);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Root folder $escapedPath was not found\.$/");

        $path = $this->getFullPath($filename);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Checks that method _load_ calls data loader's method _load_ for each file in the given folder.
     *
     * @param string $path
     * @param string $filenameRegExp
     * @param array $expectedFilesMap map used to convert a key in the resulting array to path to file
     *
     * @dataProvider directoryWithFilesDataProvider
     */
    public function testLoadCallsDataLoaderLoadForEachFile(
        string $path,
        string $filenameRegExp,
        array $expectedFilesMap
    ): void {
        $this->prepareVFSDirectoryStructure();
        $fullPath = $this->getFullPath($path);
        $expectedFilenames = array_values($expectedFilesMap);
        $dataLoaderMockCallback = static function (MockInterface $dataLoaderMock) use ($expectedFilenames, $fullPath) {
            foreach ($expectedFilenames as $expectedFile) {
                $dataLoaderMock->shouldReceive('load')->withArgs(["$fullPath/$expectedFile"])->once();
            }
        };
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $directoryDataLoader->load($fullPath, $filenameRegExp);
    }

    /**
     * Provides correct test data for testing method _load_.
     *
     * @return array [ [ path, mask, expectedFilesMap, expectedLoadData ], ... ]
     */
    public function directoryWithFilesDataProvider(): array
    {
        $multiMap = ['OtherFile' => 'other_file.json', 'JSONFile' => 'j_s_o_n_file.json'];
        $multiDirMap = array_merge(
            ['FileFirst' => 'first/file_first.exe', 'FileSecond' => 'first/file_second.exe'],
            ['FileThird' => 'second/file_third.exe', 'SomeExt' => 'some_ext.exe']
        );
        $singleLoadData = ['OneFile' => ['param1' => 'some_value']];
        $multiLoadData = ['OtherFile' => ['param2' => 'other_value'], 'JSONFile' => ['param3' => 'last_value']];
        $multiDirLoadData = array_merge(
            ['FileFirst' => ['param4' => 'no_value'],  'FileSecond' => ['param5' => 'ext_value']],
            ['FileThird' => ['param6' => 'this_value'], 'SomeExt' => ['param7' => 'that_value']]
        );
        return [
            'single file' => ['single', '/^.*\..*$/', ['OneFile' => 'one_file.json'], $singleLoadData],
            'multiple files with mask' => ['multi', '/^.*\.json$/', $multiMap, $multiLoadData],
            'multiple directories' => ['container', '/^.*\.exe$/', $multiDirMap, $multiDirLoadData]
        ];
    }

    /**
     * Prepares directory structure in virtual file system for testing method _load_.
     */
    private function prepareVFSDirectoryStructure(): void
    {
        $structure = [
            'single' => ['one_file.json' => ''],
            'multi' => ['other_file.json' => '', 'j_s_o_n_file.json' => '', 'text.txt' => ''],
            'container' => [
                'first' => ['file_first.exe' => '', 'file_second.exe' => '', 'text.txt' => ''],
                'second' => ['file_third.exe' => '', 'other.dat' => ''],
                'third' => ['temp.tmp' => ''],
                'some_ext.exe' => ''
            ]
        ];
        $this->addStructureToVFS($structure);
    }

    /**
     * Checks that method _load_ returns array combined from arrays returned from data loaders' method _load_.
     *
     * @param string $path
     * @param string $filenameRegExp
     * @param array $expectedFilesMap map used to convert a key in the resulting array to path to file
     * @param array $expectedLoadData
     *
     * @dataProvider directoryWithFilesDataProvider
     */
    public function testLoadReturnsResultOfLoadOfDataLoaders(
        string $path,
        string $filenameRegExp,
        array $expectedFilesMap,
        array $expectedLoadData
    ): void {
        $this->prepareVFSDirectoryStructure();
        $fullPath = $this->getFullPath($path);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap, $expectedLoadData);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $actualLoadData = $directoryDataLoader->load($fullPath, $filenameRegExp);

        static::assertArraysAreSame($expectedLoadData, $actualLoadData);
    }

    /**
     * Prepares function that takes mock object for data loader and performs its additional preparation.
     *
     * @param string $path
     * @param array $expectedFilesMap map used to convert a key in the resulting array to path to file
     * @param array|null $expectedLoadData
     *
     * @return callable
     */
    private function prepareDataLoaderMockCallback(
        string $path,
        array $expectedFilesMap,
        ?array $expectedLoadData = null
    ): callable {
        return static function (MockInterface $dataLoaderMock) use ($expectedFilesMap, $expectedLoadData, $path) {
            foreach ($expectedFilesMap as $key => $expectedFile) {
                $filePath = "$path/$expectedFile";
                $returnData = $expectedLoadData[$key] ?? [];
                $dataLoaderMock->shouldReceive('load')->withArgs([$filePath])->andReturn($returnData);
            }
        };
    }

    /**
     * Checks that method _getLastErrors_ returns errors for files with same test data container names.
     */
    public function testGetLastErrorsReturnsErrorsForSameFilenames(): void
    {
        $dirname = 'same';
        $this->prepareVFSDirectoryWithSameFilenames($dirname);
        $expectedFilesMap = ['diff_ext/file_one.exe', 'diff_ext/file_one.json', 'second/file_one.exe', 'file_one.exe'];
        $expectedErrors = $this->prepareExpectedErrors($dirname);
        $fullPath = $this->getFullPath($dirname);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);
        $directoryDataLoader->load($fullPath, '/^.*\..*$/');

        $actualErrors = $directoryDataLoader->getLastErrors();

        static::assertArraysAreSame($expectedErrors, $actualErrors);
    }

    /**
     * Prepares directory structure in virtual file system that contains files with same names.
     *
     * @param string $dirname tested root folder
     */
    private function prepareVFSDirectoryWithSameFilenames(string $dirname): void
    {
        $structure = [
            $dirname => [
                'diff_ext' => ['file_one.exe' => '', 'file_one.json' => ''],
                'second' => ['file_one.exe' => ''],
                'file_one.exe' => ''
            ]
        ];
        $this->addStructureToVFS($structure);
    }

    /**
     * Prepares and returns array of expected errors for testing _getLastErrors_ method.
     *
     * @param string $dirname tested root folder
     *
     * @return array
     */
    private function prepareExpectedErrors(string $dirname): array
    {
        $errorMessage = 'Classname of test data container FileOne is already in use';
        $errorKeys = [
            $this->getFullPath("$dirname/diff_ext/file_one.json"),
            $this->getFullPath("$dirname/second/file_one.exe"),
            $this->getFullPath("$dirname/file_one.exe")
        ];
        return array_fill_keys($errorKeys, $errorMessage);
    }

    /**
     * Checks that method _getLastErrors_ returns errors that occurred during files' processing.
     */
    public function testGetLastErrorsReturnsErrorsThrownByDataLoaders(): void
    {
        $this->prepareVFSDirectoryStructure();
        $fullPath = $this->getFullPath('container');
        $expectedFilesMap = ['FileSecond' => 'first/file_second.exe', 'SomeExt' => 'some_ext.exe'];
        $expectedExceptions = $this->prepareExpectedExceptions($fullPath);
        $dataLoaderMockCallback =
            $this->prepareDataLoaderMockCallbackWithExceptions($fullPath, $expectedFilesMap, null, $expectedExceptions);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);
        $directoryDataLoader->load($fullPath, '/^.*\.exe$/');
        $errorMapper = static function (Exception $exception) {
            return $exception->getMessage();
        };
        $expectedErrors = array_map($errorMapper, $expectedExceptions);

        $actualErrors = $directoryDataLoader->getLastErrors();

        static::assertArraysAreSame($expectedErrors, $actualErrors);
    }

    /**
     * Prepares array with expected exceptions for processed files.
     *
     * @param string $dirname tested root folder
     *
     * @return array
     */
    private function prepareExpectedExceptions(string $dirname): array
    {
        return [
            "$dirname/first/file_first.exe" => new Exception('Some error occurred'),
            "$dirname/second/file_third.exe" => new Exception('Another error found')
        ];
    }

    /**
     * Prepares function that takes mock object for data loader and performs its additional preparation considering
     * thrown exceptions.
     *
     * @param string $path
     * @param array $expectedFilesMap map used to convert a key in the resulting array to path to file
     * @param array|null $expectedLoadData
     * @param array $expectedFilesExceptions
     *
     * @return callable
     */
    private function prepareDataLoaderMockCallbackWithExceptions(
        string $path,
        array $expectedFilesMap,
        ?array $expectedLoadData,
        array $expectedFilesExceptions
    ): callable {
        $callback = $this->prepareDataLoaderMockCallback($path, $expectedFilesMap, $expectedLoadData);
        return static function (MockInterface $dataLoaderMock) use ($callback, $expectedFilesExceptions) {
            $callback($dataLoaderMock);
            foreach ($expectedFilesExceptions as $expectedFile => $exception) {
                $dataLoaderMock->shouldReceive('load')->withArgs([$expectedFile])->andThrow($exception);
            }
        };
    }

    /**
     * Checks that method _load_ returns loading results even if errors occurred during processing of some files.
     */
    public function testLoadReturnsCorrectDataWithExceptionsThrownByDataLoaders(): void
    {
        $this->prepareVFSDirectoryStructure();
        $fullPath = $this->getFullPath('container');
        $expectedFilesMap = ['FileSecond' => 'first/file_second.exe', 'SomeExt' => 'some_ext.exe'];
        $expectedLoadData = ['FileSecond' => ['param1' => 'with_value'], 'SomeExt' => ['param2' => 'without_value']];
        $expectedExceptions =
            array_fill_keys(["$fullPath/first/file_first.exe", "$fullPath/second/file_third.exe"], new Exception());
        $dataLoaderMockCallbackParams = [$fullPath, $expectedFilesMap, $expectedLoadData, $expectedExceptions];
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallbackWithExceptions(...$dataLoaderMockCallbackParams);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $actualLoadData = $directoryDataLoader->load($fullPath, '/^.*\.exe$/');

        static::assertArraysAreSame($expectedLoadData, $actualLoadData);
    }
}
