<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

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
     * Загружает данные в контейнер.
     *
     * @return object   загруженный контейнер с данными
     */
    public function load(): object;
}