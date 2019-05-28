<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\Exceptions\DataFileNotFoundException;

/**
 * Интерфейс загрузчика данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DataLoader
{
    /**
     * Загружает данные из файла, обрабатывает и возвращает массив.
     *
     * @param string    $filename    наименование и путь к файлу с данными
     *
     * @return array    массив с обработанными данными
     *
     * @throws DataFileNotFoundException    не найден файл с данными
     */
    public function load(string $filename): array;

    /**
     * Возвращает класс процессора данных.
     *
     * @return string    класс процессора данных
     */
    public function getDataProcessorClass(): string;
}
