<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

/**
 * Фабрика обработчиков данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataProcessorFactory
{
    /**
     * Создаёт обработчик данных для JSON-файла, содержащего данные для тестов.
     *
     * @return DataProcessor    обработчик данных
     */
    public function createTestContainerDataProcessor(): DataProcessor
    {
        return new TestContainerTestDataProcessor();
    }

}