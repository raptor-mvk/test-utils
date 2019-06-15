<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
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
    public static function createTestContainerWrapperDataLoader(): DataLoader
    {
        $dataProcessor = new TestContainerWrapperDataProcessor();
        return new BaseDataLoader($dataProcessor);
    }

    /**
     * Создаёт загрузчик данных в формате JSON, используемый для генератора вспомогательного файла для IDE.
     *
     * @return DataLoader    загрузчик данных
     */
    public static function createTestContainerGeneratorDataLoader(): DataLoader
    {
        $dataProcessor = new TestContainerGeneratorDataProcessor();
        return new BaseDataLoader($dataProcessor);
    }
}
