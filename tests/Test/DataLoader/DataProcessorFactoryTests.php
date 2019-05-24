<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataProcessor\DataProcessorFactory;
use Raptor\Test\DataProcessor\TestContainerTestDataProcessor;

/**
 * Класс с тестами для фабрики обработчиков данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataProcessorFactoryTests extends TestCase
{
    public function testCreateTestContainerDataProcessorReturnsTestContainerDataProcessorInstance(): void
    {
        $dataProcessorFactory = new DataProcessorFactory();

        $actual = $dataProcessorFactory->createTestContainerDataProcessor();

        static::assertInstanceOf(TestContainerTestDataProcessor::class, $actual);
    }
}