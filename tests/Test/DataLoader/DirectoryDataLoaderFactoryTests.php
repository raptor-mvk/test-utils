<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\DirectoryDataLoader;
use Raptor\Test\DataLoader\DirectoryDataLoaderFactory;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;

/**
 * Класс с тестами для фабрики загрузчиков данных из всех файлов (по маске) в директории.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DirectoryDataLoaderFactoryTests extends TestCase
{
    /**
     * Проверяет, что фабричный метод возвращает экземпляр _DirectoryDataLoader_.
     *
     * @param string    $factoryMethod    наименование фабричного метода
     *
     * @dataProvider factoryMethodsProvider
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод класса DirectoryDataLoaderFactory
     */
    public function testFactoryMethodReturnsDirectoryDataLoaderInstance(string $factoryMethod): void
    {
        $actual = DirectoryDataLoaderFactory::$factoryMethod();

        static::assertInstanceOf(DirectoryDataLoader::class, $actual);
    }

    /**
     * Предоставляет тестовые данные для тестирования фабричных методов.
     *
     * @return array    массив тестовых данных в формате [ [ factoryMethod, expectedDataProcessorClass ], ... ]
     */
    public function factoryMethodsProvider(): array
    {
        return [
            'createTestContainerGeneratorDataLoader' =>
                ['createTestContainerGeneratorDataLoader', TestContainerGeneratorDataProcessor::class]
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
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод класса DirectoryDataLoaderFactory
     */
    public function testFactoryMethodUsesCorrectDataProcessor(
        string $factoryMethod,
        string $expectedDataProcessorClass
    ): void {
        /** @var DirectoryDataLoader $directoryDataLoader */
        $directoryDataLoader = DirectoryDataLoaderFactory::$factoryMethod();

        $actualDataProcessorClass = $directoryDataLoader->getDataProcessorClass();

        static::assertSame($expectedDataProcessorClass, $actualDataProcessorClass);
    }
}
