<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\DataLoader\DataProcessor\DataProcessor;
use Raptor\Test\Exceptions\DataFileNotFoundException;
use Throwable;

/**
 * Базовая реализация загрузчика данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class BaseDataLoader implements DataLoader
{
    /** @var string $containerClass          наименование класса контейнера для данных */
    private $containerClass;

    /** @var DataProcessor $dataProcessor    обработчик данных */
    private $dataProcessor;

    /** @var string $filename                путь к файлу с данными */
    private $filename;

    /**
     * Конструктор загрузчика данных.
     *
     * @param string $containerClass        наименование класса контейнера для данных
     * @param DataProcessor $dataProcessor  обработчик данных
     * @param string $filename              путь к файлу с данными
     */
    public function __construct(string $containerClass, DataProcessor $dataProcessor, string $filename)
    {
        $this->containerClass = $containerClass;
        $this->dataProcessor = $dataProcessor;
        $this->filename = $filename;
    }

    /**
     * Непосредственно выполняет загрузку данных из файла в массив для провайдера данных для теста.
     *
     * @return array   набор тестов
     */
    private function loadData(): array
    {
        $data = file_get_contents($this->filename);
        $processedData = $this->dataProcessor->process($data);
        $result = [];
        foreach ($processedData as $key => $value) {
            $result[$key] = new $this->containerClass($value);
        }
        return $result;
    }

    /** @noinspection PhpDocMissingThrowsInspection __approved__ */
    /** проброс исключения на уровень выше, новые классы не добавляются */
    /**
     * Загружает данные из файла в массив для провайдера данных для теста.
     *
     * @return array                        набор тестов
     *
     * @throws DataFileNotFoundException    не найден файл с данными
     */
    public function load(): array
    {
        try {
            return $this->loadData();
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                throw new DataFileNotFoundException("Не найден файл с данными $this->filename", 0, $e);
            }
            /** @noinspection PhpUnhandledExceptionInspection __approved__ */
            throw $e; /** проброс исключения на уровень выше, новые классы не добавляются */
        }
    }
}
