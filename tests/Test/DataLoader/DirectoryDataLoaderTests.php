<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataLoader\DirectoryDataLoader;
use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\Test\Exceptions\DataDirectoryNotFoundException;
use Raptor\Test\ExtraAssertions;
use Raptor\Test\WithVFS;

/**
 * Класс с тестами для базовой реализации загрузчика данных из всех файлов (по маске) в директории
 * _DirectoryDataLoader_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DirectoryDataLoaderTests extends TestCase
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
        $directoryDataLoader = new DirectoryDataLoader($dataLoader);
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
        $dirname = 'nonexistent';
        $escapedPath = $this->getEscapedFullPath($dirname);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath($dirname);
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
    private function prepareDirectoryDataLoader(?callable $dataLoaderMockCallback = null): DirectoryDataLoader
    {
        $dataLoaderMock = Mockery::mock(DataLoader::class);
        if ($dataLoaderMockCallback !== null) {
            $dataLoaderMockCallback($dataLoaderMock);
        }
        return new DirectoryDataLoader($dataLoaderMock);
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataDirectoryNotFoundException_, если директория с данными
     * недоступна для чтения.
     */
    public function testLoadThrowsDataDirectoryNotFoundForNonReadableDirectory(): void
    {
        $dirname = 'forbidden';
        $this->addDirectoryToVFS($dirname, 0);
        $escapedPath = $this->getEscapedFullPath($dirname);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath($dirname);
        $directoryDataLoader = $this->prepareDirectoryDataLoader();

        $directoryDataLoader->load($path, '/^.*\..*$/');
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataDirectoryNotFoundException_, если директория с данными
     * является файлом.
     */
    public function testLoadThrowsDataDirectoryNotFoundForFileInsteadOfDirectory(): void
    {
        $filename = 'accessible.json';
        $this->addFileToVFS($filename);
        $escapedPath = $this->getEscapedFullPath($filename);
        $this->expectException(DataDirectoryNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найдена директория с данными $escapedPath$/");

        $path = $this->getFullPath($filename);
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
        $this->addStructureToVFS($this->prepareDirectoryWithSingleFile());
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleFiles());
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleSubdirectories());
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
        $this->addStructureToVFS($this->prepareDirectoryWithSingleFile());
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleFiles());
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleSubdirectories());
        $fullPath = $this->getFullPath($path);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap, $expectedLoadData);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);

        $actualLoadData = $directoryDataLoader->load($fullPath, $mask);

        static::assertArraysAreSame($expectedLoadData, $actualLoadData);
    }

    /**
     * Готовит функцию, принимающая на вход mock-объект загрузчика данных и выполняющая его дополнительную подготовку.
     *
     * @param string        $path                путь к разбираемой директории
     * @param array         $expectedFilesMap    массив отображений имени ключа в результирующем массиве на путь к файлу
     * @param array|null    $expectedLoadData    ожидаемый результат

     * @return callable    функцию подготовки mock-объекта загрузчика данных
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
     * Проверяет, что метод _getLastErrors_ возвращает ошибки для файлов с одинаковыми названиями классов контейнеров.
     */
    public function testGetLastErrorsReturnsErrorsForSameFilenames(): void
    {
        $dirname = 'same';
        $this->addStructureToVFS($this->prepareDirectoryWithSameFilenames($dirname));
        $expectedFilesMap = ['diff_ext/file_one.exe', 'diff_ext/file_one.json', 'second/file_one.exe', 'file_one.exe'];
        $mapper = function ($path) use ($dirname) {
            return $this->getFullPath("$dirname/$path");
        };
        $expectedErrorFiles = array_map($mapper, ['diff_ext/file_one.json', 'second/file_one.exe', 'file_one.exe']);
        $expectedErrors = array_fill_keys($expectedErrorFiles, 'Повторяющееся наименование класса контейнера');
        $fullPath = $this->getFullPath($dirname);
        $dataLoaderMockCallback = $this->prepareDataLoaderMockCallback($fullPath, $expectedFilesMap);
        $directoryDataLoader = $this->prepareDirectoryDataLoader($dataLoaderMockCallback);
        $directoryDataLoader->load($fullPath, '/^.*\..*$/');

        $actualErrors = $directoryDataLoader->getLastErrors();

        static::assertArraysAreSame($expectedErrors, $actualErrors);
    }

    /**
     * Готовит структуру каталогов из директории, содержащей одинаковые имена файлов.
     *
     * @param string    $dirname    имя директории
     *
     * @return array    массив наименований файлов
     */
    private function prepareDirectoryWithSameFilenames(string $dirname): array
    {
        return [
            $dirname => [
                'diff_ext' => ['file_one.exe' => '', 'file_one.json' => ''],
                'second' => ['file_one.exe' => ''],
                'file_one.exe' => ''
            ]
        ];
    }

    /**
     * Проверяет, что метод _getLastErrors_ возвращает ошибки, возникающие при обработке файлов.
     */
    public function testGetLastErrorsReturnsErrorsThrownByDataLoaders(): void
    {
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleSubdirectories());
        $fullPath = $this->getFullPath('container');
        $expectedFilesMap = ['FileSecond' => 'first/file_second.exe', 'SomeExt' => 'some_ext.exe'];
        $expectedExceptions = ["$fullPath/first/file_first.exe" => new Exception('Some error occurred')];
        $expectedExceptions["$fullPath/second/file_third.exe"] = new Exception('Another error found');
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
     * Готовит функцию, принимающая на вход mock-объект загрузчика данных и выполняющая его дополнительную подготовку с
     * учётом выбрасываемых исключений.
     *
     * @param string        $path                       путь к разбираемой директории
     * @param array         $expectedFilesMap           массив отображений имени ключа в результирующем массиве на путь
     *                                                  к файлу
     * @param array|null    $expectedLoadData           ожидаемый результат
     * @param array         $expectedFilesExceptions    ожидаемые исключения

     * @return callable    функцию подготовки mock-объекта загрузчика данных
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
     * Проверяет, что метод _load_ возвращает результаты загрузки при возникновении ошибок для некоторых файлов.
     */
    public function testLoadReturnsCorrectDataWithExceptionsThrownByDataLoaders(): void
    {
        $this->addStructureToVFS($this->prepareDirectoryWithMultipleSubdirectories());
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
