<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

use Raptor\Test\DataProcessor\DataProcessorFactory;

/**
 * Фабрика загрузчиков данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataLoaderFactory
{
    /** @var DataProcessorFactory $dataProcessorFactory */
    private $dataProcessorFactory;

    /**
     * Конструктор фабрики.
     *
     * @param DataProcessorFactory  $dataProcessorFactory   фабрика обработчиков данных
     */
    public function __construct(DataProcessorFactory $dataProcessorFactory)
    {
        $this->dataProcessorFactory = $dataProcessorFactory;
    }

    /**
     * Создаёт загрузчик данных в массив, содержащий контейнеры данных для тестов.
     *
     * @return DataLoader   загрузчик данных
     */
    public function createTestContainerDataLoader(): DataLoader
    {
        $dataProcessor = $this->dataProcessorFactory->createTestContainerDataProcessor();
        return new BaseDataLoader($dataProcessor);
    }
}