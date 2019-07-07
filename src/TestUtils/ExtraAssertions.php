<?php
declare(strict_types=1);

namespace Raptor\TestUtils;

use function is_array;

/**
 * Trait with additional assertions.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 * @author Igor Vodka
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraAssertions
{
    /**
     * Asserts that two arrays are same.
     *
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     */
    public static function assertArraysAreSame(array $expected, array $actual, ?string $message = null): void
    {
        $expectedString = static::jsonEncodePrettily($expected);
        $actualString = static::jsonEncodePrettily($actual);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }

    /**
     * Asserts that two associative arrays contain same elements ignoring their order at the top level.
     *
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     */
    public static function assertArraysAreSameIgnoringOrder(
        array $expected,
        array $actual,
        ?string $message = null
    ): void {
        ksort($expected);
        ksort($actual);
        $expectedString = static::jsonEncodePrettily($expected);
        $actualString = static::jsonEncodePrettily($actual);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }

    /**
     * Sorts array inline by key recursively.
     *
     * @param array $array
     */
    private static function ksortRecursive(array &$array): void
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                static::ksortRecursive($array[$key]);
            }
        }
    }

    /**
     * Asserts that two associative arrays contains same elements ignoring their order at every level.
     *
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     */
    public static function assertArraysAreSameIgnoringOrderRecursively(
        array $expected,
        array $actual,
        ?string $message = null
    ): void {
        static::ksortRecursive($expected);
        static::ksortRecursive($actual);
        $expectedString = static::jsonEncodePrettily($expected);
        $actualString = static::jsonEncodePrettily($actual);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }


    /**
     * Encodes JSON: unescapes Unicode, prettifies output and throws exceptions on errors.
     *
     * @param mixed $input input
     *
     * @return string encoded JSON string
     */
    private static function jsonEncodePrettily($input): string
    {
        return json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
