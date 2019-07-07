<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\TestDataContainerGenerator;

use Mockery;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DirectoryDataLoader;
use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\Generator\TestDataContainerGenerator;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class TestDataContainerGeneratorTests extends TestCase
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

        static::assertStringEqualsFile($referencePath, $actualResult);
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
            'int_value' => new IntType(),
            'float_value' => new FloatType(),
            'bool_value' => new BoolType(),
            'is_correct' => new BoolType(),
            'string_value' => new StringType(),
            'array_value' => new ArrayType(),
            'mixed_value' => new MixedType()
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
            'int' => new IntType(),
            'float' => new FloatType(),
            'mixed' => new MixedType()
        ];
    }
}
