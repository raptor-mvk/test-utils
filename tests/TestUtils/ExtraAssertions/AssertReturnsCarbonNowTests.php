<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\ExtraAssertions;

use Carbon\Carbon;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;

/**
 * Tests for _ExtraAssertions::assertReturnsCarbonNow_.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class AssertReturnsCarbonNowTests extends TestCase
{
    use ExtraAssertions;

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
}
