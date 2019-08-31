<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests\ExtraAssertions;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertionsTrait;

/**
 * Tests for _ExtraAssertions::assertStringsAreSameIgnoringEOL_.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class AssertStringsAreSameIgnoringEOLTests extends TestCase
{
    use ExtraAssertionsTrait;

    /**
     * Checks that assertion _assertStringsAreSameIgnoringEOL_ accepts correct strings
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
            '\r\r\n vs \n\n' => ["two breaks\r\r\nlast", "two breaks\n\nlast"],
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
    public function testAssertStringsAreSameIgnoringEOLRejectsIncorrectFunction(string $expected, string $actual, string $message): void
    {
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
            '\r\r\n vs \n' => ["double\r\r\nbreak", "double\nbreak", 'strange thing'],
        ];
    }
}
