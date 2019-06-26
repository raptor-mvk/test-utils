<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\TestDataContainer;

use function is_array;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainerTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _allData_ returns array passed to constructor.
     *
     * @param array $data
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
     * Provides test data for testing method _allData_.
     *
     * @return array [ [ data ], ... ]
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
     * Checks that getters return correct values.
     *
     * @param array $data
     * @param string $getter
     * @param mixed $expected
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
     * Provides test data for testing getters.
     *
     * @return array [ [ data, getter, expected ], ... ]
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
