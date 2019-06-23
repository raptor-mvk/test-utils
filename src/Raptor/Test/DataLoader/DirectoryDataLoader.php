<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\Exceptions\DataDirectoryNotFoundException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Throwable;

/**
 * Загрузчик данных из всех файлов (по маске) в директории.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DirectoryDataLoader
{
    /** @var DataLoader    $dataLoader    загрузчик данных */
    private $dataLoader;

    /** @var array    $lastErrors    массив ошибок, возникших при последней загрузке */
    private $lastErrors;

    /** @var array    $processedData    обработанные данные */
    private $processedData;

    /**
     * Конструктор загрузчика данных из всех файлов (по маске) в директории.
     *
     * @param DataLoader    $dataLoader    загрузчик данных
     */
    public function __construct(DataLoader $dataLoader)
    {
        $this->dataLoader = $dataLoader;
    }

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
    public function load(string $path, string $filenameRegExp): array
    {
        $this->lastErrors = [];
        $this->processedData = [];
        if (!is_readable($path) || !is_dir($path)) {
            throw new DataDirectoryNotFoundException("Не найдена директория с данными $path");
        }
        $directoryIteratorFlags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        $directoryIterator = new RecursiveDirectoryIterator($path, $directoryIteratorFlags);
        $mode = RecursiveIteratorIterator::LEAVES_ONLY;
        $recursiveIteratorFlags = RecursiveIteratorIterator::CATCH_GET_CHILD;
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, $mode, $recursiveIteratorFlags);
        $iterator = new RegexIterator($recursiveIterator, $filenameRegExp);
        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $this->processFile($fileInfo->getPath(), $fileInfo->getFilename(), $fileInfo->getExtension());
        }
        return $this->processedData;
    }

    /**
     * Обрабатывает файл загрузчиком данных.
     *
     * @param string    $path         путь к файлу
     * @param string    $filename     имя файла
     * @param string    $extension    расширение файла
     */
    private function processFile(string $path, string $filename, string $extension): void
    {
        $key = ucfirst(str_replace('_', '', ucwords(basename($filename, ".$extension"), '_')));
        $unixPath = str_replace('\\', '/', $path);
        $filePath = "$unixPath/$filename";
        if (isset($this->processedData[$key])) {
            $this->lastErrors[$filePath] = 'Повторяющееся наименование класса контейнера';
            return;
        }
        try {
            $this->processedData[$key] = $this->dataLoader->load($filePath);
        } catch (Throwable $exception) {
            $this->lastErrors[$filePath] = $exception->getMessage();
        }
    }

    /**
     * Возвращает класс процессора данных.
     *
     * @return string    класс процессора данных
     */
    public function getDataProcessorClass(): string
    {
        return $this->dataLoader->getDataProcessorClass();
    }

    /**
     * Возвращает массив ошибок, возникших во время последней загрузки. Ключами массива служат имена файлов.
     *
     * @return array    массив ошибок
     */
    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }
}
