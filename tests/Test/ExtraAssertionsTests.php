<?php
declare(strict_types=1);

namespace RaptorTests\Test;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для трейта `ExtraAssertions`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class ExtraAssertionsTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Провайдер данных, предоставляющий одинаковые массивы с разным порядком следования элементов.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function equalArraysWithDifferentOrderDataProvider(): array
    {
        return [
            'associative' => [['пять' => 346, 'семь' => 644], ['семь' => 644, 'пять' => 346]],
            'nested associative' => [
                ['a' => ['b' => 'c'], 'd' => ['e' => ['f' => 'g']]],
                ['d' => ['e' => ['f' => 'g']], 'a' => ['b' => 'c']]
            ]
        ];
    }

    /**
     * Провайдер данных, предоставляющий разные массивы и сообщения об ошибке.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual, message ], ... ]
     */
    public function differentArraysDataProvider(): array
    {
        return [
            'just different' => [['весна', '2', 3], [4, 'лето', '3'], 'different arrays'],
            'scalar, different order' => [[4, 16, 7], [7, 16, 4], 'scalar, different order'],
            'nested scalar, different order' => [
                [1, [4, 6], [4, [8, 3]]],
                [[4, 6], [4, [8, 3]], 1],
                'nested scalar, different order'
            ],
            'array and subarray' => [[1, 2, 3], [1, 2], 'subarray'],
            'integer and integer in quotes' => [[1, '2', 3], [1, 2, 3], 'integer in string'],
            'float and float in quotes' => [[89, 10, '6.3'], [89, 10, 6.3], 'float in string'],
            'bool and bool in quotes' => [[45, 'true'], [45, true], 'bool in string']
        ];
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSame_ принимает одинаковые массивы.
     *
     * @param array     $array  ожидаемый и полученный массив
     *
     * @dataProvider equalArraysWithDifferentOrderDataProvider
     */
    public function testAssertArraysAreSameAcceptsEqualArrays(array $array): void
    {
        static::assertArraysAreSame($array, $array);
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSame_ отвергает одинаковые массивы с разным порядком следования
     * элементов.
     *
     * @param array $expected   ожидаемый массив
     * @param array $actual     полученный массив
     *
     * @dataProvider equalArraysWithDifferentOrderDataProvider
     */
    public function testAssertArraysAreSameDeclinesEqualArraysWithDifferentOrder(array $expected, array $actual): void
    {
        $this->expectException(ExpectationFailedException::class);

        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSame_ отвергает разные массивы и выводит сообщение об ошибке.
     *
     * @param array     $expected   ожидаемый массив
     * @param array     $actual     полученный массив
     * @param string    $message    сообщение об ошибке
     *
     * @dataProvider differentArraysDataProvider
     */
    public function testAssertArraysAreSameDeclinesDifferentArraysWithCorrectMessage(
        array $expected,
        array $actual,
        string $message
    ): void {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertArraysAreSame($expected, $actual, $message);
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrder_ принимает одинаковые массивы.
     *
     * @param array     $array  ожидаемый и полученный массив
     *
     * @dataProvider equalArraysWithDifferentOrderDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderAcceptsEqualArrays(array $array): void
    {
        static::assertArraysAreSameIgnoringOrder($array, $array);
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrder_ приниает одинаковые массивы с разным порядком
     * следования элементов.
     *
     * @param array     $expected   ожидаемый массив
     * @param array     $actual     полученный массив
     *
     * @dataProvider equalArraysWithDifferentOrderDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderAcceptsEqualArraysWithDifferentOrder(
        array $expected,
        array $actual
    ): void {
        static::assertArraysAreSameIgnoringOrder($expected, $actual);
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrder_ отвергает разные массивы и выводит сообщение об
     * ошибке.
     *
     * @param array     $expected   ожидаемый массив
     * @param array     $actual     полученный массив
     * @param string    $message    сообщение об ошибке
     *
     * @dataProvider differentArraysDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderDeclinesDifferentArraysWithCorrectMessage(
        array $expected,
        array $actual,
        string $message
    ): void {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertArraysAreSameIgnoringOrder($expected, $actual, $message);
    }
}
