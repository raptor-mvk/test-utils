<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\BaseDataLoader;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataLoader\DataLoaderFactory;
use Raptor\Test\DataProcessor\TestContainerWrapperDataProcessor;

/**
 * Класс с тестами для фабрики загрузчиков данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataLoaderFactoryTests extends TestCase
{
    /**
     * Проверяет, что фабричный метод возвращает экземпляр _BaseDataLoader_.
     *
     * @param string    $factoryMethod    наименование фабричного метода
     *
     * @dataProvider factoryMethodsProvider
     */
    public function testFactoryMethodReturnsBaseDataLoaderInstance(string $factoryMethod): void
    {
        $dataLoaderFactory = new DataLoaderFactory();

        $actual = $dataLoaderFactory->$factoryMethod();

        static::assertInstanceOf(BaseDataLoader::class, $actual);
    }

    /**
     * Предоставляет тестовые данные для тестирования фабричных методов.
     *
     * @return array    массив тестовых данных в формате [ [ factoryMethod, expectedDataProcessorClass ], ... ]
     */
    public function factoryMethodsProvider(): array
    {
        return [
            'createTestContainerDataLoader' =>
                ['createTestContainerDataLoader', TestContainerWrapperDataProcessor::class]
        ];
    }

    /**
     * Проверяет, что фабричный метод использует корректный обработчик данных.
     *
     * @param string    $factoryMethod                 наименование фабричного метода
     * @param string    $expectedDataProcessorClass    ожидаемый класс обработчика данных
     *
     * @dataProvider factoryMethodsProvider
     */
    public function testFactoryMethodUsesCorrectDataProcessor(
        string $factoryMethod,
        string $expectedDataProcessorClass
    ): void {
        $dataLoaderFactory = new DataLoaderFactory();
        /** @var DataLoader $dataLoader */
        $dataLoader = $dataLoaderFactory->$factoryMethod();

        $actualDataProcessorClass = $dataLoader->getDataProcessorClass();

        static::assertSame($expectedDataProcessorClass, $actualDataProcessorClass);
    }
}
