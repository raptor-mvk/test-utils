<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\Test\Exceptions\DataFileNotFoundException;
use Raptor\Test\ExtraAssertions;
use Raptor\Test\WithVFS;

/**
 * Класс с тестами для загрузчика данных _DataLoader_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataLoaderTests extends TestCase
{
    use MockeryPHPUnitIntegration, ExtraAssertions, WithVFS;

    /**
     * Подготовка тестового окружения.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupVFS();
    }

    /**
     * Проверяет, что метод _getDataProcessorClass_ возвращает корректный класс.
     *
     * @param DataProcessor    $dataProcessor    обработчик данных
     * @param string           $expectedClass    ожидаемый класс обработчика данных
     *
     * @dataProvider dataProcessorClassDataProvider
     */
    public function testGetDataProcessorClassReturnsCorrectClass(
        DataProcessor $dataProcessor,
        string $expectedClass
    ): void {
        $dataLoader = new DataLoader($dataProcessor);
        $actualClass = $dataLoader->getDataProcessorClass();
        static::assertSame($expectedClass, $actualClass);
    }

    /**
     * Предоставляет тестовые данные для тестирования метода _getDataProcessorClass_.
     *
     * @return array    массив тестовых данных в формате [ [ dataProcessor, expectedClass ], ... ]
     */
    public function dataProcessorClassDataProvider(): array
    {
        $result = [
            'wrapper' => [new TestContainerWrapperDataProcessor(), TestContainerWrapperDataProcessor::class],
            'generator' => [new TestContainerGeneratorDataProcessor(), TestContainerGeneratorDataProcessor::class]
        ];
        return $result;
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными не найден.
     */
    public function testLoadThrowsDataFileNotFoundForNonExistingFile(): void
    {
        $filename = 'nonexistent.json';
        $escapedFilename = $this->getEscapedFullPath($filename);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $fullFilename = $this->getFullPath($filename);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Готовит загрузчик данных с mock-объектом для процессора данных.
     *
     * @param callable|null    $dataProcessorMockCallback    функция, принимающая на вход mock-объект процессора данных
     * и выполняющая дополнительную подготовку mock-объекта процессора данных
     *
     * @return DataLoader    загрузчик данных
     */
    private function prepareDataLoader(?callable $dataProcessorMockCallback = null): DataLoader
    {
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        if ($dataProcessorMockCallback !== null) {
            $dataProcessorMockCallback($dataProcessorMock);
        }
        return new DataLoader($dataProcessorMock);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными недоступен для
     * чтения.
     */
    public function testLoadThrowsDataFileNotFoundForNonReadableFile(): void
    {
        $filename = 'forbidden.json';
        $this->addFileToVFS($filename, 0);
        $escapedFilename = $this->getEscapedFullPath($filename);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $fullFilename = $this->getFullPath($filename);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными является
     * директорией.
     */
    public function testLoadThrowsDataFileNotFoundForDirectoryInsteadOfFile(): void
    {
        $dirname = 'accessible_dir';
        $this->addDirectoryToVFS($dirname);
        $escapedFilename = $this->getEscapedFullPath($dirname);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $fullFilename = $this->getFullPath($dirname);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($fullFilename);
    }

    /**
     * Проверяет, что метод _load_ вызывает метод _process_ обработчика данных, передавая данные из файла.
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
     * Проверяет, что метод _load_ возвращает массив, полученный из результата вызова метода _process_ обработчика
     * данных оборачиванием элементов массива в контейнеры.
     */
    public function testLoadReturnsResultOfProcessWithWrappedElements(): void
    {
        $filename = 'accessible_too.json';
        $this->addFileToVFS($filename);
        $processMockData = $this->getProcessMockData();
        $fullFilename = $this->getFullPath($filename);
        $dataProcessorMockCallback = static function (MockInterface $dataProcessorMock) use ($processMockData) {
            $dataProcessorMock->shouldReceive('process')->andReturn($processMockData);
        };
        $dataLoader = $this->prepareDataLoader($dataProcessorMockCallback);

        $actualData = $dataLoader->load($fullFilename);

        static::assertArraysAreSame($processMockData, $actualData);
    }

    /**
     * Возвращает тестовые данные для подмены результата вызова метода _process_ обработчика данных.
     *
     * @return array    тестовые данные
     */
    private function getProcessMockData(): array
    {
        return [
            'test1' => ['param1' => 'some_value'],
            'test2' => ['param1' => 'other_value', 'param2' => 'no_value'],
            'test3' => ['param1' => 'last_value', 'param2' => 'extra_value', 'param5' => ['empty_value', 'this_value']]
        ];
    }
}
