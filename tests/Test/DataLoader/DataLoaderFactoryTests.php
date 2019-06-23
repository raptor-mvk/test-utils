<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\DataLoader;
use Raptor\Test\DataLoader\DataLoaderFactory;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
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
     * Проверяет, что фабричный метод возвращает экземпляр _DataLoader_.
     *
     * @param string    $factoryMethod    наименование фабричного метода
     *
     * @dataProvider factoryMethodsProvider
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод класса DataLoaderFactory
     */
    public function testFactoryMethodReturnsDataLoaderInstance(string $factoryMethod): void
    {
        $actual = DataLoaderFactory::$factoryMethod();

        static::assertInstanceOf(DataLoader::class, $actual);
    }

    /**
     * Предоставляет тестовые данные для тестирования фабричных методов.
     *
     * @return array    массив тестовых данных в формате [ [ factoryMethod, expectedDataProcessorClass ], ... ]
     */
    public function factoryMethodsProvider(): array
    {
        return [
            'wrapper' => ['createTestContainerWrapperDataLoader', TestContainerWrapperDataProcessor::class],
            'generator' => ['createTestContainerGeneratorDataLoader', TestContainerGeneratorDataProcessor::class]
        ];
    }

    /**
     * Проверяет, что фабричный метод использует корректный обработчик данных.
     *
     * @param string    $factoryMethod                 наименование фабричного метода
     * @param string    $expectedDataProcessorClass    ожидаемый класс обработчика данных
     *
     * @dataProvider factoryMethodsProvider
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод класса DataLoaderFactory
     */
    public function testFactoryMethodUsesCorrectDataProcessor(
        string $factoryMethod,
        string $expectedDataProcessorClass
    ): void {
        /** @var DataLoader $dataLoader */
        $dataLoader = DataLoaderFactory::$factoryMethod();

        $actualDataProcessorClass = $dataLoader->getDataProcessorClass();

        static::assertSame($expectedDataProcessorClass, $actualDataProcessorClass);
    }
}
