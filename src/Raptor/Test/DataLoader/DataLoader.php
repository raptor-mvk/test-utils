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
     * Загружает данные из файла в массив для провайдера данных для теста.
     *
     * @return array                        набор тестов
     *
     * @throws DataFileNotFoundException    не найден файл с данными
     */
    public function load(): array;
}
