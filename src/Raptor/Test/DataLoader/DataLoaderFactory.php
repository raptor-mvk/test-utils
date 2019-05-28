<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;

/**
 * Фабрика загрузчиков данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataLoaderFactory
{
    /**
     * Создаёт загрузчик данных в массив, содержащий контейнеры данных для тестов.
     *
     * @return DataLoader    загрузчик данных
     */
    public function createTestContainerDataLoader(): DataLoader
    {
        $dataProcessor = new TestContainerWrapperDataProcessor();
        return new BaseDataLoader($dataProcessor);
    }
}
