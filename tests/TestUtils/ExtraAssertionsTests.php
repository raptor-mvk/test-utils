<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\WithVFS;

/**
 * Класс с тестами для трейта _ExtraAssertions_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class ExtraAssertionsTests extends TestCase
{
    use ExtraAssertions, WithVFS;

    /**
     * Подготовка тестового окружения.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupVFS();
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSame_ принимает заданные массивы.
     *
     * @param array    $expected    ожидаемый массив
     * @param array    $actual      полученный массив
     *
     * @dataProvider acceptableArraysAreSameDataProvider
     */
    public function testAssertArraysAreSameAcceptsProvidedArrays(array $expected, array $actual): void
    {
        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение _assertArraysAreSame_ должно принимать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function acceptableArraysAreSameDataProvider(): array
    {
        return $this->prepareSameArraysTestData();
    }

    /**
     * Готовит тестовые данные с одинаковыми массивами.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    private function prepareSameArraysTestData(): array
    {
        $associative = ['пять' => 346, 'семь' => 644];
        $nestedAssociative = ['a' => ['b' => 'c'], 'd' => ['e' => ['f' => 'g']]];
        return [
            'associative' => [$associative, $associative],
            'nested associative' => [$nestedAssociative, $nestedAssociative]
        ];
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSame_ отвергает заданные массивы.
     *
     * @param array     $expected    ожидаемый массив
     * @param array     $actual      полученный массив
     * @param string    $message     сообщение об ошибке
     *
     * @dataProvider rejectableArraysAreSameDataProvider
     */
    public function testAssertArraysAreSameDeclinesProvidedArrays(array $expected, array $actual, string $message): void
    {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertArraysAreSame($expected, $actual, $message);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение _assertArraysAreSame_ должно отвергать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function rejectableArraysAreSameDataProvider(): array
    {
        return array_merge(
            $this->prepareEqualArraysWithDifferentOrderTestData(),
            $this->prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData(),
            $this->prepareDifferentArraysTestData()
        );
    }

    /**
     * Готовит тестовые данные с одинаковыми массивами с разным порядком следования элементов на верхнем уровне.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual, message ], ... ].
     */
    public function prepareEqualArraysWithDifferentOrderTestData(): array
    {
        return [
            'associative' => [['два' => 1253, 'четыре' => 367], ['четыре' => 367, 'два' => 1253], 'ошибка № 463'],
            'nested associative' => [
                ['a' => ['b' => 'c'], 'd' => ['e' => ['f' => 'g']]],
                ['d' => ['e' => ['f' => 'g']], 'a' => ['b' => 'c']],
                'странная ошибка'
            ]
        ];
    }

    /**
     * Готовит тестовые данные с одинаковыми массивами с разным порядком следования элементов на более низких уровнях.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual, message ], ... ].
     */
    public function prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData(): array
    {
        return [
            'second_level' => [
                ['a' => ['b' => 'c', 'd' => 'e'], 'f' => ['g' => ['h' => 'i']]],
                ['a' => ['d' => 'e', 'b' => 'c'], 'f' => ['g' => ['h' => 'i']]],
                'уровень 2'
            ],
            'third_level' => [
                ['a' => ['b' => 'c', 'd' => ['e' => 'f', 'g' => 'h']], 'i' => ['j' => ['k' => 'l']]],
                ['a' => ['b' => 'c', 'd' => ['g' => 'h', 'e' => 'f']], 'i' => ['j' => ['k' => 'l']]],
                'уровень 3'
            ]
        ];
    }

    /**
     * Готовит тестовые данные с разными массивами.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual, message ], ... ]
     */
    public function prepareDifferentArraysTestData(): array
    {
        return [
            'just different' => [['весна', '2', 3], [4, 'лето', '3'], 'different arrays'],
            'simple, different order' => [[4, 16, 7], [7, 16, 4], 'scalar, different order'],
            'nested simple, different order' => [
                [1, [4, 6], [4, [8, 3]]],
                [[4, 6], [4, [8, 3]], 1],
                'nested simple, different order'
            ],
            'array and subarray' => [[1, 2, 3], [1, 2], 'subarray'],
            'integer and integer in quotes' => [[1, '2', 3], [1, 2, 3], 'integer in string'],
            'float and float in quotes' => [[89, 10, '6.3'], [89, 10, 6.3], 'float in string'],
            'bool and bool in quotes' => [[45, 'true'], [45, true], 'bool in string']
        ];
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrder_ принимает заданные массивы.
     *
     * @param array    $expected    ожидаемый массив
     * @param array    $actual      полученный массив
     *
     * @dataProvider acceptableArraysAreSameIgnoringOrderDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderAcceptsProvidedArrays(array $expected, array $actual): void
    {
        static::assertArraysAreSameIgnoringOrder($expected, $actual);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение _assertArraysAreSameIgnoringOrder_ должно
     * принимать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function acceptableArraysAreSameIgnoringOrderDataProvider(): array
    {
        return array_merge(
            $this->prepareSameArraysTestData(),
            $this->prepareEqualArraysWithDifferentOrderTestData()
        );
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrder_ отвергает заданные массивы.
     *
     * @param array     $expected    ожидаемый массив
     * @param array     $actual      полученный массив
     * @param string    $message     сообщение об ошибке
     *
     * @dataProvider rejectableArraysAreSameIgnoringOrderDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderDeclinesProvidedArrays(
        array $expected,
        array $actual,
        string $message
    ): void {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertArraysAreSameIgnoringOrder($expected, $actual, $message);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение _assertArraysAreSameIgnoringOrder_ должно
     * отвергать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function rejectableArraysAreSameIgnoringOrderDataProvider(): array
    {
        return array_merge(
            $this->prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData(),
            $this->prepareDifferentArraysTestData()
        );
    }
    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrderRecursively_ принимает заданные массивы.
     *
     * @param array    $expected    ожидаемый массив
     * @param array    $actual      полученный массив
     *
     * @dataProvider acceptableArraysAreSameIgnoringOrderRecursivelyDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderRecursivelyAcceptsProvidedArrays(
        array $expected,
        array $actual
    ): void {
        static::assertArraysAreSameIgnoringOrderRecursively($expected, $actual);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение
     * _assertArraysAreSameIgnoringOrderRecursively_ должно принимать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function acceptableArraysAreSameIgnoringOrderRecursivelyDataProvider(): array
    {
        return array_merge(
            $this->prepareSameArraysTestData(),
            $this->prepareEqualArraysWithDifferentOrderTestData(),
            $this->prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData()
        );
    }

    /**
     * Проверяет, что утверждение _assertArraysAreSameIgnoringOrderRecursively_ отвергает заданные массивы.
     *
     * @param array     $expected    ожидаемый массив
     * @param array     $actual      полученный массив
     * @param string    $message     сообщение об ошибке
     *
     * @dataProvider rejectableArraysAreSameIgnoringOrderRecursivelyDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderRecursivelyDeclinesProvidedArrays(
        array $expected,
        array $actual,
        string $message
    ): void {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertArraysAreSameIgnoringOrderRecursively($expected, $actual, $message);
    }

    /**
     * Провайдер данных, предоставляющий тестовые данные, которые утверждение
     * _assertArraysAreSameIgnoringOrderRecursively_ должно отвергать.
     *
     * @return array    массив тестовых данных в формате [ [ expected, actual ], ... ].
     */
    public function rejectableArraysAreSameIgnoringOrderRecursivelyDataProvider(): array
    {
        return $this->prepareDifferentArraysTestData();
    }

    /**
     * Проверяет, что утверждение _assertStringIsSameAsFile_ принимает строку, совпадающую с содержимым файла.
     */
    public function testAssertStringIsSameAsFileAcceptsCorrectString(): void
    {
        $content = 'some_string';
        $filename = 'correct.txt';
        $this->addFileToVFS($filename, null, $content);
        $path = $this->getFullPath($filename);

        static::assertStringIsSameAsFile($path, $content);
    }

    /**
     * Проверяет, что утверждение _assertStringIsSameAsFile_ отвергает строку, не совпадающую с содержимым файла.
     */
    public function testAssertStringIsSameAsFileRejectsIncorrectString(): void
    {
        $message = 'String is wrong';
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        $filename = 'incorrect.txt';
        $this->addFileToVFS($filename, null, 'some_string');
        $path = $this->getFullPath($filename);

        static::assertStringIsSameAsFile($path, 'other_string', $message);
    }
}
