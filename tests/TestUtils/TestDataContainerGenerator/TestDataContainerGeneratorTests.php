<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\TestDataContainerGenerator;

use Mockery;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DirectoryDataLoader;
use Raptor\TestUtils\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainerGenerator\TestDataContainerGenerator;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainerGeneratorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _generate_ returns correct result.
     *
     * @param array $directoryDataLoaderResult
     * @param string $referenceFilename name of the reference file with expected result
     *
     * @dataProvider generateDataProvider
     */
    public function testGenerateReturnsCorrectResult(array $directoryDataLoaderResult, string $referenceFilename): void
    {
        $referencePath = __DIR__."/reference/$referenceFilename";
        $path = 'some_path';
        $directoryDataLoader = Mockery::mock(DirectoryDataLoader::class);
        $directoryDataLoader->shouldReceive('load')
                            ->withArgs([$path, '/^.*\.json$/'])
                            ->andReturn($directoryDataLoaderResult);
        $testDataContainerGenerator = new TestDataContainerGenerator($directoryDataLoader);

        $actualResult = $testDataContainerGenerator->generate($path);

        static::assertStringIsSameAsFile($referencePath, $actualResult);
    }

    /**
     * Provides test data for testing method _generate_.
     *
     * @return array [ [ directoryDataLoaderResult, expectedReferenceFilename ], ... ]
     */
    public function generateDataProvider(): array
    {
        return [
            'single class' => [['FileOne' => $this->prepareDirectoryDataLoaderAllTypesClassData()], 'single_class.txt'],
            'multiple classes' => [
                [
                    'FileOne' => $this->prepareDirectoryDataLoaderAllTypesClassData(),
                    'FileTwo' => $this->prepareDirectoryDataLoaderSecondClassData()
                ],
                'multi_classes.txt'
            ]
        ];
    }

    /**
     * Prepares test data with all data types.
     *
     * @return array
     */
    private function prepareDirectoryDataLoaderAllTypesClassData(): array
    {
        return [
            'int_value' => TestContainerGeneratorDataProcessor::INT_TYPE,
            'float_value' => TestContainerGeneratorDataProcessor::FLOAT_TYPE,
            'bool_value' => TestContainerGeneratorDataProcessor::BOOL_TYPE,
            'is_correct' => TestContainerGeneratorDataProcessor::BOOL_TYPE,
            'string_value' => TestContainerGeneratorDataProcessor::STRING_TYPE,
            'array_value' => TestContainerGeneratorDataProcessor::ARRAY_TYPE,
            'mixed_value' => TestContainerGeneratorDataProcessor::MIXED_TYPE
        ];
    }

    /**
     * Prepares test data for second class.
     *
     * @return array
     */
    private function prepareDirectoryDataLoaderSecondClassData(): array
    {
        return [
            'int' => TestContainerGeneratorDataProcessor::INT_TYPE,
            'float' => TestContainerGeneratorDataProcessor::FLOAT_TYPE,
            'mixed' => TestContainerGeneratorDataProcessor::MIXED_TYPE
        ];
    }
}
