<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\BaseDataLoader;
use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\Exceptions\DataFileNotFoundException;
use Raptor\Test\Exceptions\DataParseException;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для базовой реализации загрузчика данных `BaseDataLoader`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDataLoaderTests extends TestCase
{
    use MockeryPHPUnitIntegration, ExtraAssertions;

    /** @var vfsStreamDirectory     $root       виртуальная файловая система */
    private $root;

    /** @var string                 $filename   имя временного JSON-файла с тестовыми данными */
    private $filename;

    /** @var string                 $contents   содержимое временного JSON-файла с тестовыми данными */
    private $contents;

    /**
     * Подготовка окружения.
     *
     * @SuppressWarnings(PHPMD.StaticAccess) фабричный метод _newFile_
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup();
        $this->filename = 'some_file.json';
        $this->contents = json_encode(['some_key' => 'some_value']);
        $this->root->addChild(vfsStream::newFile($this->filename)->withContent($this->contents));
        $this->filename = $this->root->url() . "/$this->filename";
    }

    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными не найден.
     */
    public function testLoadThrowsDataFileNotFoundForNonExistingFile(): void
    {
        $filename = 'some_file';
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Не найден файл с данными $filename$/");

        /** @var DataProcessor $dataProcessorMock */
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        $dataLoader = new BaseDataLoader($dataProcessorMock);

        $dataLoader->load($filename);
    }

    /**
     * Проверяет, что метод _load_ перепробрасывает исключение, не связанное с не найденным файлом, из обработчика
     * данных.
     */
    public function testLoadRethrowsDataParseException(): void
    {
        $message = 'Some error occurred';
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^$message$/");

        /** @var DataProcessor|MockInterface $dataProcessorMock */
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        $dataProcessorMock->shouldReceive('process')->andThrow(new DataParseException($message));
        $dataLoader = new BaseDataLoader($dataProcessorMock);

        $dataLoader->load($this->filename);
    }

    /**
     * Проверяет, что метод _load_ вызывает метод _process_ обработчика данных, передавая данные из файла.
     */
    public function testLoadCallsDataProcessorProcess(): void
    {
        /** @var DataProcessor|MockInterface $dataProcessorMock */
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        $dataProcessorMock->shouldReceive('process')->withArgs([$this->contents])->once();
        $dataLoader = new BaseDataLoader($dataProcessorMock);

        $dataLoader->load($this->filename);
    }

    /**
     * Возвращает тестовые данные для подмены результата вызова метода _process_ обработчика данных.
     *
     * @return array    тестовые данные
     */
    private function getTestData(): array
    {
        return [
            'test1' => ['param1' => 'some_value'],
            'test2' => ['param1' => 'other_value', 'param2' => 'no_value'],
            'test3' => ['param1' => 'last_value', 'param2' => 'extra_value', 'param5' => ['empty_value', 'this_value']]
        ];
    }

    /**
     * Проверяет, что метод _load_ возвращает массив, полученный из результата вызова метода _process_ обработчика
     * данных оборачиванием элементов массива в контейнеры.
     */
    public function testLoadReturnsResultOfProcessWithWrappedElements(): void
    {
        $testData = $this->getTestData();
        /** @var DataProcessor|MockInterface $dataProcessorMock */
        $dataProcessorMock = Mockery::mock(DataProcessor::class);
        $dataProcessorMock->shouldReceive('process')->andReturn($testData);
        $dataLoader = new BaseDataLoader($dataProcessorMock);

        $actualData = $dataLoader->load($this->filename);

        static::assertArraysAreSame($testData, $actualData);
    }
}
