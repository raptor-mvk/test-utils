<?php
declare(strict_types=1);

namespace Raptor\TestUtils;

use function is_array;

/**
 * Trait with additional assertions.
 *
 * @author Mikhail Kamorin aka raptor_MVK
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
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }
}
