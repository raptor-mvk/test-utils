<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для обработчика тестовых данных в формате JSON, используемый для генератора вспомогательного файла
 * для IDE `TestContainerGeneratorDataProcessor`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainerGeneratorDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _process_ возвращает коректный результат.
     *
     * @param string    $json        JSON-строка для обработки
     * @param array     $expected    ожидаемый результат
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsCorrectResult(string $json, array $expected): void
    {
        $dataProcessor = new TestContainerGeneratorDataProcessor();

        $actual = $dataProcessor->process($json);

        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Предоставляет корректные тестовые данные для метода _process_.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    public function correctDataProvider(): array
    {
        return [
            'single occurrence' => $this->prepareSingleOccurrenceTestData(),
            'multi occurrence with same type' => $this->prepareMultiOccurrenceWithSameTypeTestData(),
            'multi occurrence with different types' => $this->prepareMultiOccurrenceWithDifferentTypeTestData(),
            'float => int and int => float' => $this->prepareMultiOccurrenceWithFloatAndIntTestData()
        ];
    }

    /**
     * Готовит тестовые данные, где поля встречаются единожды.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    private function prepareSingleOccurrenceTestData(): array
    {
        $jsonData = array_merge(
            ['int_field' => 3, 'string_field' => 'test', 'float_field' => 56.35, 'bool_field' => true],
            ['associative_array_field' => ['a' => 23, 'b' => 7], 'simple_array_field' => [46, 764]],
            ['null_field' => null]
        );
        $json = json_encode([array_merge($jsonData, ['_name' => 'some_test'])]);
        $expected = [
            'int_field' => TestContainerGeneratorDataProcessor::INT_TYPE,
            'string_field' => TestContainerGeneratorDataProcessor::STRING_TYPE,
            'float_field' => TestContainerGeneratorDataProcessor::FLOAT_TYPE,
            'bool_field' => TestContainerGeneratorDataProcessor::BOOL_TYPE,
            'associative_array_field' => TestContainerGeneratorDataProcessor::ARRAY_TYPE,
            'simple_array_field' => TestContainerGeneratorDataProcessor::ARRAY_TYPE,
            'null_field' => TestContainerGeneratorDataProcessor::MIXED_TYPE
        ];
        return [$json, $expected];
    }

    /**
     * Готовит тестовые данные, где поля встречаются несколько раз с одинаковым типом.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    private function prepareMultiOccurrenceWithSameTypeTestData(): array
    {
        $data = array_merge(
            ['int_field' => 3, 'string_field' => 'test', 'float_field' => 56.35, 'bool_field' => true],
            ['associative_array_field' => ['a' => 23, 'b' => 7], 'simple_array_field' => [46, 764]],
        );
        $jsonData = [array_merge($data, ['_name' => 'some_test']), array_merge($data, ['_name' => 'other_test'])];
        $json = json_encode($jsonData);
        $expected = [
            'int_field' => TestContainerGeneratorDataProcessor::INT_TYPE,
            'string_field' => TestContainerGeneratorDataProcessor::STRING_TYPE,
            'float_field' => TestContainerGeneratorDataProcessor::FLOAT_TYPE,
            'bool_field' => TestContainerGeneratorDataProcessor::BOOL_TYPE,
            'associative_array_field' => TestContainerGeneratorDataProcessor::ARRAY_TYPE,
            'simple_array_field' => TestContainerGeneratorDataProcessor::ARRAY_TYPE
        ];
        return [$json, $expected];
    }

    /**
     * Готовит тестовые данные, где поля встречаются несколько раз с разными типами.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    private function prepareMultiOccurrenceWithDifferentTypeTestData(): array
    {
        $firstData = array_merge(
            ['int_field' => 3, 'string_field' => 'test', 'float_field' => 56.35, 'bool_field' => true],
            ['associative_array_field' => ['a' => 23, 'b' => 7], 'simple_array_field' => [46, 764]],
        );
        $secondData = array_merge(
            ['int_field' => 'text', 'string_field' => [35, 646], 'float_field' => ['a' => 5], 'bool_field' => 5.36],
            ['associative_array_field' => 1324, 'simple_array_field' => true],
        );
        $jsonData = [array_merge($firstData, ['_name' => 'test1']), array_merge($secondData, ['_name' => 'test2'])];
        $json = json_encode($jsonData);
        $fields =
            ['int_field', 'string_field', 'float_field', 'bool_field', 'associative_array_field', 'simple_array_field'];
        $expected = array_fill_keys($fields, TestContainerGeneratorDataProcessor::MIXED_TYPE);
        return [$json, $expected];
    }

    /**
     * Готовит тестовые данные, где поля встречаются несколько раз с типами (float, int) в разном порядке.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    private function prepareMultiOccurrenceWithFloatAndIntTestData(): array
    {
        $firstData = ['int_field' => 3, 'float_field' => 56.35];
        $secondData = ['int_field' => 64.14, 'float_field' => 43];
        $jsonData = [array_merge($firstData, ['_name' => 'test1']), array_merge($secondData, ['_name' => 'test2'])];
        $json = json_encode($jsonData);
        $expected = [
            'int_field' => TestContainerGeneratorDataProcessor::FLOAT_TYPE,
            'float_field' => TestContainerGeneratorDataProcessor::FLOAT_TYPE
        ];
        return [$json, $expected];
    }
}
