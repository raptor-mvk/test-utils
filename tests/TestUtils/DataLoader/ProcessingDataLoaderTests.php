<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataLoader;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DataLoader;
use Raptor\TestUtils\DataLoader\ProcessingDataLoader;
use Raptor\TestUtils\DataProcessor\DataProcessor;
use Raptor\TestUtils\Exceptions\DataFileNotFoundException;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\WithVFS;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class ProcessingDataLoaderTests extends TestCase
{
    use MockeryPHPUnitIntegration, ExtraAssertions, WithVFS;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupVFS();
    }

    /**
     * Checks that method _load_ throws _DataFileNotFoundException_, if data file was not found.
     */
    public function testLoadThrowsDataFileNotFoundForNonExistingFile(): void
    {
        $filename = 'nonexistent.json';
        $escapedFilename = $this->getEscapedFullPath($filename);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Data file $escapedFilename was not found\.$/");

        $fullFilename = $this->getFullPath($filename);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Prepare data loader with mock object for data processor.
     *
     * @param callable|null $dataProcessorMockCallback function that takes mock object for data processor and performs
     *                                                 its additional preparation
     * @return DataLoader
     */
    private function prepareDataLoader(?callable $dataProcessorMockCallback = null): DataLoader
    {
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        if ($dataProcessorMockCallback !== null) {
            $dataProcessorMockCallback($dataProcessorMock);
        }
        return new ProcessingDataLoader($dataProcessorMock);
    }

    /**
     * Checks that method _load_ throws _DataFileNotFoundException_, if read access to the data file is denied.
     */
    public function testLoadThrowsDataFileNotFoundForNonReadableFile(): void
    {
        $filename = 'forbidden.json';
        $this->addFileToVFS($filename, 0);
        $escapedFilename = $this->getEscapedFullPath($filename);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Data file $escapedFilename was not found.$/");

        $fullFilename = $this->getFullPath($filename);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Checks that _load_ throws _DataFileNotFoundException_, if the data file is actually a directory.
     */
    public function testLoadThrowsDataFileNotFoundForDirectoryInsteadOfFile(): void
    {
        $dirname = 'accessible_dir';
        $this->addDirectoryToVFS($dirname);
        $escapedFilename = $this->getEscapedFullPath($dirname);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Data file $escapedFilename was not found.$/");

        $fullFilename = $this->getFullPath($dirname);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Checks that method _load_ calls data loader's method _process_ with contents of the given file as a parameter.
     */
    public function testLoadCallsDataProcessorProcess(): void
    {
        $filename = 'accessible.json';
        $contents = json_encode(['some_key' => 'some_value']);
        $this->addFileToVFS($filename, null, $contents);
        $fullFilename = $this->getFullPath($filename);
        $dataProcessorMockCallback = static function (MockInterface $dataProcessorMock) use ($contents) {
            $dataProcessorMock->shouldReceive('process')->withArgs([$contents])->once();
        };
        $dataLoader = $this->prepareDataLoader($dataProcessorMockCallback);

        $dataLoader->load($fullFilename);
    }

    /**
     * Checks that method _load_ returns an array created from the array returned by data loader's method _process_ by
     * wrapping each element in TestDataContainer.
     */
    public function testLoadReturnsResultOfProcessWithWrappedElements(): void
    {
        $filename = 'accessible_too.json';
        $this->addFileToVFS($filename);
        $processMockData = $this->prepareProcessMockData();
        $fullFilename = $this->getFullPath($filename);
        $dataProcessorMockCallback = static function (MockInterface $dataProcessorMock) use ($processMockData) {
            $dataProcessorMock->shouldReceive('process')->andReturn($processMockData);
        };
        $dataLoader = $this->prepareDataLoader($dataProcessorMockCallback);

        $actualData = $dataLoader->load($fullFilename);

        static::assertArraysAreSame($processMockData, $actualData);
    }

    /**
     * Prepare test data for mock return value of data loader's method _process_.
     *
     * @return array
     */
    private function prepareProcessMockData(): array
    {
        return [
            'test1' => ['param1' => 'some_value'],
            'test2' => ['param1' => 'other_value', 'param2' => 'no_value'],
            'test3' => ['param1' => 'last_value', 'param2' => 'extra_value', 'param5' => ['empty_value', 'this_value']]
        ];
    }
}
