<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils;

use Carbon\Carbon;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\LogicalNot;
use function is_array;

/**
 * Trait with additional assertions.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 * @author Igor Vodka
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraAssertionsTrait
{
    /**
     * Asserts that two arrays are same.
     *
     * @param array       $expected
     * @param array       $actual
     * @param string|null $message
     */
    public static function assertArraysAreSame(array $expected, array $actual, ?string $message = null): void
    {
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

    /**
     * Asserts that two associative arrays contain same elements ignoring their order at the top level.
     *
     * @param array       $expected
     * @param array       $actual
     * @param string|null $message
     */
    public static function assertArraysAreSameIgnoringOrder(array $expected, array $actual, ?string $message = null): void
    {
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
     * @param array       $expected
     * @param array       $actual
     * @param string|null $message
     */
    public static function assertArraysAreSameIgnoringOrderRecursively(array $expected, array $actual, ?string $message = null): void
    {
        static::ksortRecursive($expected);
        static::ksortRecursive($actual);
        $expectedString = static::jsonEncodePrettily($expected);
        $actualString = static::jsonEncodePrettily($actual);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }

    /**
     * Asserts that the given function returns result of _Carbon::now()_, invoked while running.
     *
     * @param callable    $func
     * @param string|null $message
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method LogicalAnd::fromConstraints
     */
    public static function assertReturnsCarbonNow(callable $func, ?string $message = null): void
    {
        $timeGetter = static function () {
            return Carbon::now()->valueOf();
        };
        static::commonAssertReturnsCarbonNow($func, $timeGetter, $message);
    }

    /**
     * Asserts that the given function returns result that is between two timestamps got by a given time getter
     * function.
     *
     * @param callable    $func       function that is tested
     * @param callable    $timeGetter function used to receive current time
     * @param string|null $message
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method LogicalAnd::fromConstraints
     */
    private static function commonAssertReturnsCarbonNow(callable $func, callable $timeGetter, ?string $message = null): void
    {
        $timeBefore = $timeGetter();
        $result = $func();
        $actualTime = ($result instanceof Carbon) ? $result->valueOf() : 0;
        $timeAfter = $timeGetter();
        $notBeforeConstraint = new LogicalNot(new LessThan($timeBefore));
        $notAfterConstraint = new LogicalNot(new GreaterThan($timeAfter));
        $constraint = LogicalAnd::fromConstraints($notBeforeConstraint, $notAfterConstraint);
        static::assertThat($actualTime, $constraint, $message ?? '');
    }

    /**
     * Asserts that the given function returns result of _Carbon::now()_, invoked while running, with zeroed
     * microseconds.
     *
     * @param callable    $func
     * @param string|null $message
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method LogicalAnd::fromConstraints
     */
    public static function assertReturnsCarbonNowWithoutMicroseconds(callable $func, ?string $message = null): void
    {
        $timeGetter = static function () {
            return Carbon::now()->setMicro(0)->valueOf();
        };
        static::commonAssertReturnsCarbonNow($func, $timeGetter, $message);
    }

    /**
     * Asserts that two given strings are same ignoring the difference in EOL characters.
     *
     * @param string      $expected
     * @param string      $actual
     * @param string|null $message
     */
    public static function assertStringsAreSameIgnoringEOL(string $expected, string $actual, ?string $message = null): void
    {
        $wrongEOLs = ["\r\n", "\r"];
        $expected = str_replace($wrongEOLs, "\n", $expected);
        $actual = str_replace($wrongEOLs, "\n", $actual);
        static::assertSame($expected, $actual, $message ?? '');
    }
}
