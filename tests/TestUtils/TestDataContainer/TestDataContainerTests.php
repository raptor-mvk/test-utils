<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\TestDataContainer;

use function is_array;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;

/**
 * Класс с тестами для контейнера с тестовыми данными.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainerTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _allData_ возвращает все данные.
     *
     * @param array    $data    входящие и ожидаемые данные
     *
     * @dataProvider allDataDataProvider
     */
    public function testAllDataReturnsAllData(array $data): void
    {
        $container = new TestDataContainer($data);

        $actualData = $container->allData();

        static::assertArraysAreSame($data, $actualData);
    }

    /**
     * Предоставляет тестовые данные для тестирования метода _allData_.
     *
     * @return array    массив тестовых данных в формате [ [ data ], ... ]
     */
    public function allDataDataProvider(): array
    {
        return [
            'empty' => [[]],
            'linear' => [['some_field' => 'some_value', 'other_field' => 'other_value']],
            'multi-dimension' => [['field' => 'value', 'children' => ['property' => 'type']]]
        ];
    }

    /**
     * Проверяет, что геттеры возвращают корректные значения.
     *
     * @param array     $data        входящие данные
     * @param string    $getter      наименование геттера
     * @param mixed     $expected    ожидаемый результат
     *
     * @dataProvider getterDataProvider
     */
    public function testGetterReturnsCorrectValue(array $data, string $getter, $expected): void
    {
        $container = new TestDataContainer($data);

        $actual = $container->$getter();

        $assertion = is_array($expected) ? 'assertArraysAreSame' : 'assertSame';
        $this->$assertion($expected, $actual);
    }

    /**
     * Предоставляет тестовые данные для тестирования геттеров.
     *
     * @return array    массив тестовых данных в формате [ [ data, getter, expected ], ... ]
     */
    public function getterDataProvider(): array
    {
        return [
            'int' => [['int_field' => 6], 'getIntField', 6],
            'float' => [['float_field' => 16.36], 'getFloatField', 16.36],
            'bool' => [['is_bool' => true], 'isBool', true],
            'string' => [['string_field' => 'some_string'], 'getStringField', 'some_string'],
            'array' => [['array_field' => ['a' => 3, 'b' => 6]], 'getArrayField', ['a' => 3, 'b' => 6]],
            'null' => [['field' => null], 'getField', null],
            'incorrect_getter' => [[], 'getField', null]
        ];
    }
}
