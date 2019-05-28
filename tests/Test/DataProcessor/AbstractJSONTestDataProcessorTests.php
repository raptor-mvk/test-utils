<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataProcessor;

use Mockery;
use PHPUnit\Framework\TestCase;
use Raptor\Test\DataProcessor\AbstractJSONTestDataProcessor;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для абстрактного класса обработчика JSON-файлов
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class AbstractJSONTestDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _getProcessed_ возвращает _null_, если ключ не был добавлен до этого.
     */
    public function testGetProcessedReturnsNullForNotAddedKey(): void
    {
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        $actual = static::invokeMethod($dataProcessor, 'getProcessed', ['some_key']);

        static::assertNull($actual, 'Метод getProcessed должен возвращать null для ещё не добавленного ключа');
    }

    /**
     * Проверяет, что пара _getProcessed_ / _addProcessed_ работает корректно.
     *
     * @param string    $key      ключ
     * @param mixed     $value    значение
     *
     * @dataProvider addProcessedDataProvider
     */
    public function testGetProcessedAndAddProcessedWorkCorrectly(string $key, $value): void
    {
        /** @var AbstractJSONTestDataProcessor $dataProcessor */
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        static::invokeMethod($dataProcessor, 'addProcessed', [$key, $value]);
        $actual = static::invokeMethod($dataProcessor, 'getProcessed', [$key]);

        $assertion = \is_array($value) ? 'assertArraysAreSame' : 'assertSame';
        $this->$assertion($value, $actual);
    }

    /**
     * Предоставляет тестовые данные для тестирования метода _addProcessed_.
     *
     * @return array    массив тестовых данных в формате [ [ key, value ], ... ]
     */
    public function addProcessedDataProvider(): array
    {
        return [
            'int' => ['int_key', 34],
            'float' => ['float_key', 436.22],
            'bool' => ['bool_key', true],
            'string' => ['string_key', 'very_long_string'],
            'array' => ['array_key', ['a' => 3, 'b' => 5]]
        ];
    }

    /**
     * Проверяет, что метод _hasProcessed_ возвращает _false_, если ключ не был добавлен до этого.
     */
    public function testHasProcessedReturnsFalseForNotAddedKey(): void
    {
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        $actual = static::invokeMethod($dataProcessor, 'hasProcessed', ['some_key']);

        static::assertFalse($actual, 'Метод hasProcessed должен возвращать false для ещё не добавленного ключа');
    }

    /**
     * Проверяет, что метод _hasProcessed_ возвращает _true_, если ключ был добавлен до этого.
     */
    public function testHasProcessedReturnsTrueForAddedKey(): void
    {
        $key = 'some_key';
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();
        static::invokeMethod($dataProcessor, 'addProcessed', [$key, 'value']);

        $actual = static::invokeMethod($dataProcessor, 'hasProcessed', [$key]);

        static::assertTrue($actual, 'Метод hasProcessed должен возвращать true для уже добавленного ключа');
    }
}
