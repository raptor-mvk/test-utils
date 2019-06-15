<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Raptor\Test\DataLoader\BaseDataLoader;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\Test\Exceptions\DataFileNotFoundException;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для базовой реализации загрузчика данных _BaseDataLoader_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDataLoaderTests extends AbstractTestCaseWithVFS
{
    use MockeryPHPUnitIntegration, ExtraAssertions;

    /** @var string    NONEXISTENT_FILE    имя несуществующего JSON-файла */
    private const NONEXISTENT_FILE = 'nonexistent.json';

    /** @var string    ACCESSIBLE_FILE    имя доступного для чтения JSON-файла */
    private const ACCESSIBLE_FILE = 'accessible.json';

    /** @var string    FORBIDDEN_FILE    имя недоступного для чтения JSON-файла */
    private const FORBIDDEN_FILE = 'forbidden.json';

    /** @var string    ACCESSIBLE_DIR    имя доступной для чтения директории */
    private const ACCESSIBLE_DIR = 'dir';

    /** @var string    $contents    содержимое временного JSON-файла с тестовыми данными */
    private $contents;

    /**
     * Готовит виртуальную файловую систему.
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ статический интерфейс vfsStream
     */
    protected function prepareVirtualFileSystem(): void
    {
        $this->contents = json_encode(['some_key' => 'some_value']);
        $this->addFileToVFS(static::ACCESSIBLE_FILE, null, $this->contents);
        $this->addFileToVFS(static::FORBIDDEN_FILE, 0);
        $this->addDirectoryToVFS(static::ACCESSIBLE_DIR);
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
        $dataLoader = new BaseDataLoader($dataProcessor);
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
        $escapedFilename = $this->getEscapedFullPath(static::NONEXISTENT_FILE);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $filename = $this->getFullPath(static::NONEXISTENT_FILE);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($filename);
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
        return new BaseDataLoader($dataProcessorMock);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными недоступен для
     * чтения.
     */
    public function testLoadThrowsDataFileNotFoundForNonReadableFile(): void
    {
        $escapedFilename = $this->getEscapedFullPath(static::FORBIDDEN_FILE);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $filename = $this->getFullPath(static::FORBIDDEN_FILE);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($filename);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными является
     * директорией.
     */
    public function testLoadThrowsDataFileNotFoundForDirectoryInsteadOfFile(): void
    {
        $escapedFilename = $this->getEscapedFullPath(static::ACCESSIBLE_DIR);
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $escapedFilename$/");

        $filename = $this->getFullPath(static::ACCESSIBLE_DIR);
        $dataLoader = $this->prepareDataLoader();

        $dataLoader->load($filename);
    }

    /**
     * Проверяет, что метод _load_ вызывает метод _process_ обработчика данных, передавая данные из файла.
     */
    public function testLoadCallsDataProcessorProcess(): void
    {
        $filename = $this->getFullPath(self::ACCESSIBLE_FILE);
        $dataProcessorMockCallback = function (MockInterface $dataProcessorMock) {
            $dataProcessorMock->shouldReceive('process')->withArgs([$this->contents])->once();
        };
        $dataLoader = $this->prepareDataLoader($dataProcessorMockCallback);

        $dataLoader->load($filename);
    }

    /**
     * Проверяет, что метод _load_ возвращает массив, полученный из результата вызова метода _process_ обработчика
     * данных оборачиванием элементов массива в контейнеры.
     */
    public function testLoadReturnsResultOfProcessWithWrappedElements(): void
    {
        $processMockData = $this->getProcessMockData();
        $filename = $this->getFullPath(self::ACCESSIBLE_FILE);
        $dataProcessorMockCallback = static function (MockInterface $dataProcessorMock) use ($processMockData) {
            $dataProcessorMock->shouldReceive('process')->andReturn($processMockData);
        };
        $dataLoader = $this->prepareDataLoader($dataProcessorMockCallback);

        $actualData = $dataLoader->load($filename);

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
