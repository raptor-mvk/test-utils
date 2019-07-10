<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\GeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;
use Raptor\TestUtils\ExtraAssertions;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class GeneratorDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _process_ returns correct result.
     *
     * @param string $json
     * @param array $expected
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsCorrectResult(string $json, array $expected): void
    {
        $typeFactory = new GetTypeTypeFactory();
        $dataProcessor = new GeneratorDataProcessor($typeFactory);
        $expected = $this->convertForAssertion($expected);

        $actual = $dataProcessor->process($json);

        $actual = $this->convertForAssertion($actual);
        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Provides correct test data for testing method _process_.
     *
     * @return array [ [ json, expected ], ... ]
     */
    public function correctDataProvider(): array
    {
        return [
            'single occurrence' => $this->prepareSingleOccurrenceTestData(),
            'multi occurrence with same type' => $this->prepareMultiOccurrenceWithSameTypeTestData(),
            'multi occurrence with different types' => $this->prepareMultiOccurrenceWithDifferentTypeTestData(),
            'float => int and int => float' => $this->prepareMultiOccurrenceWithFloatAndIntTestData(),
            'default values' => $this->prepareDefaultValuesTestData()
        ];
    }

    /**
     * Prepares test data, where each field appears only once.
     *
     * @return array [ [ json, expected ], ... ]
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
            'int_field' => new IntType(),
            'string_field' => new StringType(),
            'float_field' => new FloatType(),
            'bool_field' => new BoolType(),
            'associative_array_field' => new ArrayType(),
            'simple_array_field' => new ArrayType(),
            'null_field' => new MixedType()
        ];
        return [$json, $expected];
    }

    /**
     * Prepares test data, where fields appear several times with same type.
     *
     * @return array [ [ json, expected ], ... ]
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
            'int_field' => new IntType(),
            'string_field' => new StringType(),
            'float_field' => new FloatType(),
            'bool_field' => new BoolType(),
            'associative_array_field' => new ArrayType(),
            'simple_array_field' => new ArrayType()
        ];
        return [$json, $expected];
    }

    /**
     * Prepares test data, where fields appear several times with different types.
     *
     * @return array [ [ json, expected ], ... ]
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
        $expected = array_fill_keys($fields, new MixedType());
        return [$json, $expected];
    }

    /**
     * Prepares test data, where fields appear several times with types float and int in a different order.
     *
     * @return array [ [ json, expected ], ... ]
     */
    private function prepareMultiOccurrenceWithFloatAndIntTestData(): array
    {
        $firstData = ['int_field' => 3, 'float_field' => 56.35];
        $secondData = ['int_field' => 64.14, 'float_field' => 43];
        $jsonData = [array_merge($firstData, ['_name' => 'test1']), array_merge($secondData, ['_name' => 'test2'])];
        $json = json_encode($jsonData);
        $expected = [
            'int_field' => new FloatType(),
            'float_field' => new FloatType()
        ];
        return [$json, $expected];
    }

    /**
     * Prepares test data with default values.
     *
     * @return array [ [ json, expected ], ... ]
     */
    private function prepareDefaultValuesTestData(): array
    {
        $jsonChildren = [['_name' => 'both int', 'second' => 3], ['_name' => 'second string']];
        $jsonData = [['first' => 135, 'second' => 'string', '_children' => $jsonChildren]];
        $json = json_encode($jsonData);
        $expected = [
            'first' => new IntType(),
            'second' => new MixedType()
        ];
        return [$json, $expected];
    }

    /**
     * Converts data for assertion: applies __toString() to each Type object.
     *
     * @param array $data
     *
     * @return array
     */
    private function convertForAssertion(array $data): array
    {
        $converter = static function ($arg) {
            return (string)$arg;
        };
        return array_map($converter, $data);
    }
}
