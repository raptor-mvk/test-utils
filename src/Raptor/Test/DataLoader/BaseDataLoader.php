<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\DataProcessor\DataProcessor;
use Raptor\Test\Exceptions\DataFileNotFoundException;

/**
 * Базовая реализация загрузчика данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDataLoader implements DataLoader
{
    /** @var DataProcessor    $dataProcessor    обработчик данных */
    private $dataProcessor;

    /**
     * Конструктор загрузчика данных.
     *
     * @param DataProcessor    $dataProcessor    обработчик данных
     */
    public function __construct(DataProcessor $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * Загружает данные из файла, обрабатывает и возвращает массив.
     *
     * @param string    $filename    наименование и путь к файлу с данными
     *
     * @return array    массив с обработанными данными
     *
     * @throws DataFileNotFoundException    не найден файл с данными
     */
    public function load(string $filename): array
    {
        if (!is_readable($filename) || !is_file($filename)) {
            throw new DataFileNotFoundException("Не найден файл с данными $filename");
        }
        $data = file_get_contents($filename);
        return $this->dataProcessor->process($data);
    }

    /**
     * Возвращает класс процессора данных.
     *
     * @return string    класс процессора данных
     */
    public function getDataProcessorClass(): string
    {
        return \get_class($this->dataProcessor);
    }
}
