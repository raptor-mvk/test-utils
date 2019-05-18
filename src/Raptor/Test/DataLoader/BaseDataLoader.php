<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\Exceptions\DataFileNotFoundException;
use Throwable;

/**
 * Базовая реализация загрузчика данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDataLoader implements DataLoader
{
    /**
     * @var string $containerClass
     */
    private $containerClass;

    /**
     * @var string $filename
     */
    private $filename;

    /**
     * Конструктор загрузчика данных.
     *
     * @param string $containerClass    наименование класса контейнера для данных
     * @param string $filename          путь к файлу с данными
     */
    public function __construct(string $containerClass, string $filename)
    {
        $this->containerClass = $containerClass;
        $this->filename = $filename;
    }

    /**
     * Загружает данные в контейнер.
     *
     * @param string $filename путь к файлу с данными
     *
     * @return object   загруженный контейнер с данными
     */
    public function load(): object
    {
        try {
            $data = file_get_contents($this->filename);
        } catch (Throwable $e) {
            throw new DataFileNotFoundException("Не найден файл с данными {$this->filename}", 0, $e);
        }
    }
}