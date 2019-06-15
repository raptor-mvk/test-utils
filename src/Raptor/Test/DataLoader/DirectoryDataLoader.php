<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\Exceptions\DataDirectoryNotFoundException;

/**
 * Интерфейс загрузчика данных из всех файлов (по маске) в директории.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DirectoryDataLoader
{
    /**
     * Загружает данные из всех файлов в директории, включая все вложенные директории. Данные по каждому файлу в
     * отдельном элементе массива с ключом, равным имени файла.
     *
     * @param string    $path              обрабатываемый путь
     * @param string    $filenameRegExp    регулярное выражение для имени файла
     *
     * @return array    массив с обработанными данными
     *
     * @throws DataDirectoryNotFoundException    не найдена директория с данными
     */
    public function load(string $path, string $filenameRegExp): array;

    /**
     * Возвращает класс процессора данных.
     *
     * @return string    класс процессора данных
     */
    public function getDataProcessorClass(): string;

    /**
     * Возвращает массив ошибок, возникших во время последней загрузки. Ключами массива служат имена файлов.
     *
     * @return array    массив ошибок
     */
    public function getLastErrors(): array;
}
