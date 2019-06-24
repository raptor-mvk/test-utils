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
 * Класс с тестами для генератора вспомогательного файла для IDE `TestDataContainerGenerator`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainerGeneratorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _generate_ возвращает корректный результат.
     *
     * @param array     $directoryDataLoaderResult    возвращаемый загрузчиком данных результат
     * @param string    $referenceFilename    имя референсного файла с ожидаемым результатам
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
     * Предоставляет данные для тестирования метода _generate_ в формате
     * [ directoryDataLoaderResult, expectedReferenceFilename ].
     *
     * @return array    тестовые данные
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
     * Готовит данные для загрузчика данных с полями всех типов.
     *
     * @return array    данные для загрузчика данных
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
     * Готовит данные для загрузчика данных для второго класса.
     *
     * @return array    данные для загрузчика данных
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
