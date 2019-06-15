<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Raptor\Test\DataLoader\BaseDataLoader;
use Raptor\Test\DataLoader\BaseDirectoryDataLoader;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataLoader\DirectoryDataLoader;
use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\Test\Exceptions\DataDirectoryNotFoundException;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для базовой реализации загрузчика данных из всех файлов (по маске) в директории
 * _BaseDirectoryDataLoader_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDirectoryDataLoaderTests extends AbstractTestCaseWithVFS
{
    use MockeryPHPUnitIntegration, ExtraAssertions;

    /** @var string    NONEXISTENT_DIR    имя несуществующей директории */
    private const NONEXISTENT_DIR = 'nonexistent';

    /** @var string    ACCESSIBLE_DIR    имя доступной для чтения директории */
    private const ACCESSIBLE_DIR = 'accessible';

    /** @var string    FORBIDDEN_DIR    имя недоступной для чтения директории */
    private const FORBIDDEN_DIR = 'forbidden';

    /** @var string    ACCESSIBLE_FILE    имя доступного для чтения файла */
    private const ACCESSIBLE_FILE = 'file.json';

    /** @var string    SAME_FILES_DIR    директория с файлами с одинаковыми именами */
    private const SAME_FILES_DIR = 'same';

    /**
     * Готовит виртуальную файловую систему.
     */
    protected function prepareVirtualFileSystem(): void
    {
        $this->addDirectoryToVFS(static::ACCESSIBLE_DIR);
        $this->addDirectoryToVFS(static::FORBIDDEN_DIR, 0);
        $this->addFileToVFS(static::ACCESSIBLE_FILE);
        $this->addStructure($this->prepareDirectoryWithSingleFile());
        $this->addStructure($this->prepareDirectoryWithMultipleFiles());
        $this->addStructure($this->prepareDirectoryWithMultipleSubdirectories());
        $this->addStructure($this->prepareDirectoryWithSameFilenames());
    }

    /**
     * Готовит структуру каталогов из директории, содержащей единственный файл.
     *
     * @return array    структура каталогов
     */
    private function prepareDirectoryWithSingleFile(): array
    {
        return ['single' => ['one_file.json' => '']];
    }

    /**
     * Готовит структуру каталогов из директории, содержащей несколько файлов.
     *
     * @return array    структура каталогов
     */
    private function prepareDirectoryWithMultipleFiles(): array
    {
        return ['multi' => ['other_file.json' => '', 'j_s_o_n_file.json' => '', 'text.txt' => '']];
    }

    /**
     * Готовит структуру каталогов из директории и нескольких поддиректорий.
     *
     * @return array    массив наименований файлов
     */
    private function prepareDirectoryWithMultipleSubdirectories(): array
    {
        return [
            'container' => [
                'first' => ['file_first.exe' => '', 'file_second.exe' => '', 'text.txt' => ''],
                'second' => ['file_third.exe' => '', 'other.dat' => ''],
                'third' => ['temp.tmp' => ''],
                'some_ext.exe' => ''
            ]
        ];
    }

    /**
     * Готовит структуру каталогов из директории, содержащей одинаковые имена файлов.
     *
     * @return array    массив наименований файлов
     */
    private function prepareDirectoryWithSameFilenames(): array
    {
        return [
            'same' => [
                'diff_ext' => ['file_one.exe' => '', 'file_one.json' => ''],
                'second' => ['file_one.exe' => ''],
                'file_one.exe' => ''
            ]
        ];
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
        $directoryDataLoader = new BaseDirectoryDataLoader($dataLoader);
        $actualClass = $directoryDataLoader->getDataProcessorClass();
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
     * Проверяет, что метод _load_ бросает исключение _DataDirectoryNotFoundException_, если директория с данными не
     * найдена.
     */
    public function testLoadThrowsDataDirectoryNotFoundForNonExistingDir(): void
    {
        $escapedPath = $this->getEscapedFullPath(static::NONEXISTENT_DIR);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath(static::NONEXISTENT_DIR);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Готовит загрузчик данных из всех файлов (по маске) в директории с mock-объектом для процессора данных.
     *
     * @param callable|null    $dataLoaderMockCallback    функция, принимающая на вход mock-объект загрузчика данных
     * и выполняющая его дополнительную подготовку
     *
     * @return DirectoryDataLoader    загрузчик данных из всех файлов (по маске) в директории
     */
    private function prepareDirectoryDataLoader(?callable $dataLoaderMockCallback = null): DirectoryDataLoader {
        $dataLoaderMock = Mockery::mock(DataLoader::class);
        if ($dataLoaderMockCallback !== null) {
            $dataLoaderMockCallback($dataLoaderMock);
        }
        return new BaseDirectoryDataLoader($dataLoaderMock);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataDirectoryNotFoundException_, если директория с данными
     * недоступна для чтения.
     */
    public function testLoadThrowsDataDirectoryNotFoundForNonReadableDirectory(): void
    {
        $escapedPath = $this->getEscapedFullPath(static::FORBIDDEN_DIR);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath(static::FORBIDDEN_DIR);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataDirectoryNotFoundException_, если директория с данными
     * является файлом.
     */
    public function testLoadThrowsDataDirectoryNotFoundForFileInsteadOfDirectory(): void
    {
        $escapedPath = $this->getEscapedFullPath(static::ACCESSIBLE_FILE);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath(static::ACCESSIBLE_FILE);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Проверяет, что метод _load_ вызывает метод _load_ загрузчика данных для каждого файла из директории.
     *
     * @param string    $path                путь к разбираемой директории
     * @param string    $mask                маска для поиска файлов
     * @param array     $expectedFilesMap    массив отображений имени ключа в результирующем массиве на путь к файлу
     *
     * @dataProvider directoryWithFilesDataProvider
     */
    public function testLoadCallsDataLoaderLoadForEachFile(string $path, string $mask, array $expectedFilesMap): void
    {
        $fullPath = $this->getFullPath($path);
        $expectedFilenames = array_values($expectedFilesMap);
        $dataLoaderMockCallback = static function (MockInterface $dataLoaderMock) use ($expectedFilenames, $fullPath) {
            foreach ($expectedFilenames as $expectedFile) {
                $dataLoaderMock->shouldReceive('load')->withArgs(["$fullPath/$expectedFile"])->once();
            }
        };
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $directoryDataLoader->load($fullPath, $mask);
    }

    /**
     * Предоставляет корректные тестовые данные для тестирования метода _load_.
     *
     * @return array    массив тестовых данных в формате [ [ path, mask, expectedFilesMap, expectedLoadData ], ... ]
     */
    public function directoryWithFilesDataProvider(): array
    {
        $multiMap = ['OtherFile' => 'other_file.json', 'JSNFile' => 'j_s_o_n_file.json'];
        $multiDirMap = array_merge(
            ['FileFirst' => 'first/file_first.exe', 'FileSecond' => 'first/file_second.exe'],
            ['FileThird' => 'second/file_third.exe', 'SomeExt' => 'some_ext.exe']
        );
        $singleLoadData = ['OneFile' => ['param1' => 'some_value']];
        $multiLoadData = ['OtherFile' => ['param2' => 'other_value'], 'JSNFile' => ['param3' => 'last_value']];
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
     * Проверяет, что метод _load_ возвращает массив, полученный из результатов вызова метода _load_ загрузчиков данных.
     *
     * @param string    $path                путь к разбираемой директории
     * @param string    $mask                маска для поиска файлов
     * @param array     $expectedFilesMap    массив отображений имени ключа в результирующем массиве на путь к файлу
     * @param array     $expectedLoadData    ожидаемый результат
     *
     * @dataProvider directoryWithFilesDataProvider
     */
    public function testLoadReturnsResultOfLoadOfDataLoaders(
        string $path,
        string $mask,
        array $expectedFilesMap,
        array $expectedLoadData
    ): void {
        $fullPath = $this->getFullPath($path);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap, $expectedLoadData);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $actualLoadData = $directoryDataLoader->load($fullPath, $mask);

        static::assertArraysAreSame($expectedLoadData, $actualLoadData, 'aaa');
    }

    /**
     * Готовит функцию, принимающая на вход mock-объект загрузчика данных и выполняющая его дополнительную подготовку.
     *
     * @param string    $path                путь к разбираемой директории
     * @param array     $expectedFilesMap    массив отображений имени ключа в результирующем массиве на путь к файлу
     * @param array     $expectedLoadData    ожидаемый результат

     * @return callable    функцию подготовки mock-объекта загрузчика данных
     */
    private function prepareDataLoaderMockCallback(
        string $path,
        array $expectedFilesMap,
        array $expectedLoadData
    ): callable {
        return static function (MockInterface $dataLoaderMock) use ($expectedFilesMap, $expectedLoadData, $path) {
            foreach ($expectedFilesMap as $key => $expectedFile) {
                $filePath = "$path/$expectedFile";
                $returnData = $expectedLoadData[$key];
                $dataLoaderMock->shouldReceive('load')->withArgs([$filePath])->andReturn($returnData);
            }
        };
    }

    /**
     * Проверяет, что метод _getLastErrors_ возвращает ошибки для файлов с одинаковыми именами.
     *
     * @param string    $path                путь к разбираемой директории
     * @param string    $mask                маска для поиска файлов
     * @param array     $expectedFilesMap    массив отображений имени ключа в результирующем массиве на путь к файлу
     * @param array     $expectedErrors      ожидаемые ошибки
     *
     * @dataProvider directoryWithFilesDataProvider
     */
    public function testGetLastErrorsReturnsErrorsForSameFilenames(
        string $path,
        string $mask,
        array $expectedFilesMap,
        array $expectedErrors
    ): void {
        $fullPath = $this->getFullPath(static::SAME_FILES_DIR);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap, $expectedLoadData);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $actualLoadData = $directoryDataLoader->load($fullPath, $mask);

        static::assertArraysAreSame($expectedLoadData, $actualLoadData);
    }

    /**
     * Предоставляет тестовые данные, содержащие одинаковые имена файлов для тестирования метода _getLastErrors_.
     *
     * @return array    массив тестовых данных в формате [ [ path, mask, expectedFilesMap, expectedErrors ], ... ]
     */
    public function sameFilenamesErrorsDataProvider(): array
    {
        $expectedErrors = [
            'diff_ext' => ['file_one.exe' => '', 'file_one.json' => ''],
            'second' => ['file_one.exe' => ''],
            'file_one.exe' => ''
        ];
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
            'single file' => ['same', '/^.*\..*$/', ['diff_ext/file_one.json' => 'Повторяющееся имя файла'], $singleLoadData],
            'multiple files with mask' => ['multi', '/^.*\.json$/', $multiMap, $multiLoadData],
            'multiple directories' => ['container', '/^.*\.exe$/', $multiDirMap, $multiDirLoadData]
        ];
    }
}
