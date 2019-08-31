<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests\ExtraAssertions;

use Carbon\Carbon;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertionsTrait;

/**
 * Tests for _ExtraAssertions::assertReturnsCarbonNow_ and _ExtraAssertions::assertReturnsCarbonNowWithoutMicroseconds_.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class AssertReturnsCarbonNowTests extends TestCase
{
    use ExtraAssertionsTrait;

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
     * @param string   $message
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
        return $this->prepareWrongAssertReturnsCarbonNowTestData();
    }

    /**
     * Checks that assertion _assertReturnsCarbonNowWithoutMicroseconds_ accepts correct function.
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method Carbon::now
     */
    public function testAssertReturnsCarbonNowWithoutMicrosecondsAcceptsCorrectFunction(): void
    {
        $function = static function () {
            return Carbon::now()->setMicro(0);
        };

        static::assertReturnsCarbonNowWithoutMicroseconds($function);
    }

    /**
     * Checks that assertion _assertReturnsCarbonNowWithoutMicroseconds_ rejects incorrect function.
     *
     * @param callable $func
     * @param string   $message
     *
     * @dataProvider assertReturnsCarbonNowWithoutMicrosecondsRejectDataProvider
     */
    public function testAssertReturnsCarbonNowWithoutMicrosecondsRejectsIncorrectFunction(callable $func, string $message): void
    {
        $messageRegExp = "/^$message\n.*$/";
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        static::assertReturnsCarbonNowWithoutMicroseconds($func, $message);
    }

    /**
     * Provides test data for _assertReturnsCarbonNowWithoutMicroseconds_ to reject.
     *
     * @return array [ [ func ], ... ]
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method Carbon::now
     */
    public function assertReturnsCarbonNowWithoutMicrosecondsRejectDataProvider(): array
    {
        $wrongCarbonMicroseconds = static function () {
            return Carbon::now()->addMicrosecond();
        };

        return array_merge(
            $this->prepareWrongAssertReturnsCarbonNowTestData(),
            ['use microseconds' => [$wrongCarbonMicroseconds, 'Microseconds are used']]
        );
    }

    /**
     * Prepares test data for _assertReturnsCarbonNow_ to reject.
     *
     * @return array [ [ func ], ... ]
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method Carbon::now
     */
    private function prepareWrongAssertReturnsCarbonNowTestData(): array
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
            'null Carbon' => [$nullCarbon, 'There is no Carbon'],
        ];
    }
}
