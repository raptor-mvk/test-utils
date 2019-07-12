<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils;

use Carbon\Carbon;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class ExtraAssertionsTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that assertion _assertArraysAreSame_ accepts given arrays.
     *
     * @param array $expected
     * @param array $actual
     *
     * @dataProvider acceptableArraysAreSameDataProvider
     */
    public function testAssertArraysAreSameAcceptsProvidedArrays(array $expected, array $actual): void
    {
        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Provides test data that assertion _assertArraysAreSame_ should accept.
     *
     * @return array [ [ expected, actual ], ... ]
     */
    public function acceptableArraysAreSameDataProvider(): array
    {
        return $this->prepareSameArraysTestData();
    }

    /**
     * Prepares test data with same arrays.
     *
     * @return array [ [ expected, actual ], ... ].
     */
    private function prepareSameArraysTestData(): array
    {
        $associative = ['five' => 346, 'seven' => 644];
        $nestedAssociative = ['a' => ['b' => 'c'], 'd' => ['e' => ['f' => 'g']]];
        return [
            'associative' => [$associative, $associative],
            'nested associative' => [$nestedAssociative, $nestedAssociative]
        ];
    }

    /**
     * Checks that assertion _assertArraysAreSame_ declines given arrays with appropriate error message.
     *
     * @param array $expected
     * @param array $actual
     * @param string $message expected error message
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
     * Provides test data that assertion _assertArraysAreSame_ should decline.
     *
     * @return array [ [ expected, actual ], ... ]
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
     * Prepares test data with arrays that contain same elements with different order at the top level.
     *
     * @return array [ [ expected, actual, message ], ... ]
     */
    public function prepareEqualArraysWithDifferentOrderTestData(): array
    {
        return [
            'associative' => [['two' => 1253, 'four' => 367], ['four' => 367, 'two' => 1253], 'error #463'],
            'nested associative' => [
                ['a' => ['b' => 'c'], 'd' => ['e' => ['f' => 'g']]],
                ['d' => ['e' => ['f' => 'g']], 'a' => ['b' => 'c']],
                'strange error'
            ]
        ];
    }

    /**
     * Prepares test data with arrays that contain same elements with different order at the lower levels.
     *
     * @return array [ [ expected, actual, message ], ... ]
     */
    public function prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData(): array
    {
        return [
            'second_level' => [
                ['a' => ['b' => 'c', 'd' => 'e'], 'f' => ['g' => ['h' => 'i']]],
                ['a' => ['d' => 'e', 'b' => 'c'], 'f' => ['g' => ['h' => 'i']]],
                'level 2'
            ],
            'third_level' => [
                ['a' => ['b' => 'c', 'd' => ['e' => 'f', 'g' => 'h']], 'i' => ['j' => ['k' => 'l']]],
                ['a' => ['b' => 'c', 'd' => ['g' => 'h', 'e' => 'f']], 'i' => ['j' => ['k' => 'l']]],
                'level 3'
            ]
        ];
    }

    /**
     * Prepares test data with different arrays.
     *
     * @return array [ [ expected, actual, message ], ... ]
     */
    public function prepareDifferentArraysTestData(): array
    {
        return [
            'just different' => [['spring', '2', 3], [4, 'summer', '3'], 'different arrays'],
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
     * Checks that assertion _assertArraysAreSameIgnoringOrder_ accepts the given arrays.
     *
     * @param array $expected
     * @param array $actual
     *
     * @dataProvider acceptableArraysAreSameIgnoringOrderDataProvider
     */
    public function testAssertArraysAreSameIgnoringOrderAcceptsProvidedArrays(array $expected, array $actual): void
    {
        static::assertArraysAreSameIgnoringOrder($expected, $actual);
    }

    /**
     * Provides test data that assertion _assertArraysAreSameIgnoringOrder_ should accept.
     *
     * @return array [ [ expected, actual ], ... ]
     */
    public function acceptableArraysAreSameIgnoringOrderDataProvider(): array
    {
        return array_merge(
            $this->prepareSameArraysTestData(),
            $this->prepareEqualArraysWithDifferentOrderTestData()
        );
    }

    /**
     * Checks that assertion _assertArraysAreSameIgnoringOrder_ declines the given arrays.
     *
     * @param array $expected
     * @param array $actual
     * @param string $message expected error message
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
     * Provides test data that assertion _assertArraysAreSameIgnoringOrder_ should decline.
     *
     * @return array [ [ expected, actual ], ... ]
     */
    public function rejectableArraysAreSameIgnoringOrderDataProvider(): array
    {
        return array_merge(
            $this->prepareEqualArraysWithDifferentOrderOnLowerLevelsTestData(),
            $this->prepareDifferentArraysTestData()
        );
    }
    /**
     * Checks that assertion _assertArraysAreSameIgnoringOrderRecursively_ accepts the given arrays.
     *
     * @param array $expected
     * @param array $actual
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
     * Provides test data that assertion _assertArraysAreSameIgnoringOrderRecursively_ should accept.
     *
     * @return array [ [ expected, actual ], ... ].
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
     * Checks that assertion _assertArraysAreSameIgnoringOrderRecursively_ declines the given arrays.
     *
     * @param array $expected
     * @param array $actual
     * @param string $message expected error message
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
     * Provides test data that assertion _assertArraysAreSameIgnoringOrderRecursively_ should decline.
     *
     * @return array [ [ expected, actual ], ... ]
     */
    public function rejectableArraysAreSameIgnoringOrderRecursivelyDataProvider(): array
    {
        return $this->prepareDifferentArraysTestData();
    }

    /**
     * Checks that assertion _assertReturnsCarbonNow_ accepts correct function.
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method Carbon::now
     */
    public function testAssertReturnsCarbonNowAcceptsCorrectFunction(): void
    {
        $function = static function () {
            return Carbon::now();
        };

        static::assertReturnsCarbonNow($function);
    }

    /**
     * Checks that assertion _assertReturnsCarbonNow_ rejects incorrect function.
     *
     * @param callable $func
     * @param string $message
     *
     * @dataProvider assertReturnsCarbonNowRejectDataProvider
     */
    public function testAssertReturnsCarbonNowRejectsIncorrectFunction(callable $func, string $message): void
    {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertReturnsCarbonNow($func, $message);
    }

    /**
     * Provides test data for _assertReturnsCarbonNow_ to reject.
     *
     * @return array [ [ func ], ... ]
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method Carbon::now
     */
    public function assertReturnsCarbonNowRejectDataProvider(): array
    {
        $wrongCarbon = static function () {
            return Carbon::now()->addDay();
        };
        $notCarbon = static function () {
            return 135;
        };
        $nullCarbon = static function () {
            return null;
        };
        return [
            'wrong Carbon' => [$wrongCarbon, 'This is wrong Carbon'],
            'not Carbon' => [$notCarbon, 'That is not Carbon'],
            'null Carbon' => [$nullCarbon, 'There is no Carbon']
        ];
    }

    /**
     * Checks that assertion _assertStringAreSameIgnoringEOL_ accepts correct strings
     *
     * @param string $expected
     * @param string $actual
     *
     * @dataProvider sameStringsIgnoringEOLDataProvider
     */
    public function testAssertStringsAreSameIgnoringEOLAcceptsCorrectStrings(string $expected, string $actual): void
    {
        static::assertStringsAreSameIgnoringEOL($expected, $actual);
    }

    /**
     * Provides test data with same strings ignoring different EOL characters.
     *
     * @return array [ [ expected, actual ], ... ]
     */
    public function sameStringsIgnoringEOLDataProvider(): array
    {
        return [
            '\n vs \r' => ["some string\nother string", "some string\rother string"],
            '\n vs \r\n' => ["some string\nother string", "some string\r\nother string"],
            '\n vs \n' => ["some string\nother string", "some string\nother string"],
            '\r vs \r' => ["other string\rdifferent string", "other string\ndifferent string"],
            '\r vs \r\n' => ["other string\rdifferent string", "other string\r\ndifferent string"],
            '\r vs \n' => ["other string\rdifferent string", "other string\ndifferent string"],
            '\r\n vs \r' => ["first string\r\nsecond string", "first string\rsecond string"],
            '\r\n vs \r\n' => ["first string\r\nsecond string", "first string\r\nsecond string"],
            '\r\n vs \n' => ["first string\r\nsecond string", "first string\nsecond string"],
            '\r\r\n vs \n\n' => ["two breaks\r\r\nlast", "two breaks\n\nlast"]
        ];
    }

    /**
     * Checks that assertion _assertStringsAreSameIgnoringEOL_ rejects incorrect strings.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     *
     * @dataProvider differentStringsIgnoringEOLDataProvider
     */
    public function testAssertStringsAreSameIgnoringEOLRejectsIncorrectFunction(
        string $expected,
        string $actual,
        string $message
    ): void {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertStringsAreSameIgnoringEOL($expected, $actual, $message);
    }

    /**
     * Provides test data with different strings ignoring different EOL characters.
     *
     * @return array [ [ expected, actual, message ], ... ]
     */
    public function differentStringsIgnoringEOLDataProvider(): array
    {
        return [
            'different' => ['some string', 'other_string', 'some error'],
            'starts from \n' => ["\nbreak", 'break', 'warning'],
            'ends from \r' => ['sentence', "sentence\r", 'error occurred'],
            '\r\r\n vs \n' => ["double\r\r\nbreak", "double\nbreak", 'strange thing']
        ];
    }
}
