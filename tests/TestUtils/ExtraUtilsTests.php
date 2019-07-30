<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\Exceptions\BadMethodException;
use Raptor\TestUtils\ExtraUtils;
use RaptorTests\TestUtils\Utils\InvokeMethodTestObject;
use RuntimeException;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class ExtraUtilsTests extends TestCase
{
    use ExtraUtils;

    /**
     * Checks that method _invokeMethod_ throws _BadMethodException_, if given method is non-existent.
     */
    public function testInvokeMethodThrowsReflectionExceptionWhenMethodIsIncorrect(): void
    {
        $method = 'replace';
        $this->expectException(BadMethodException::class);
        $this->expectExceptionMessageRegExp("/^Method $method was not found\.$/");

        $object = new InvokeMethodTestObject();

        static::invokeMethod($object, $method);
    }

    /**
     * Checks that method _invokeMethod_ invokes the given method with given parameters.
     *
     * @param string $method
     * @param array $parameters
     * @param string $expected
     *
     * @dataProvider invokeMethodDataProvider
     */
    public function testInvokeMethodCallsCorrectMethod(string $method, array $parameters, string $expected): void
    {
        $object = new InvokeMethodTestObject();

        $actual = static::invokeMethod($object, $method, $parameters);

        static::assertSame($expected, $actual);
    }

    /**
     * Provides test data for testing method _invokeMethod_.
     *
     * @return array [ [ method, parameters, expected ], ... ]
     */
    public function invokeMethodDataProvider(): array
    {
        $palindrome = 'delia saw I was ailed';
        $extra = 'a';
        $count = 2;
        return [
            'protected' => ['reverse', [$palindrome . $extra], $extra . $palindrome],
            'private' => ['repeat', [$palindrome, $count], str_repeat($palindrome, $count)]
        ];
    }

    /**
     * Checks that method _expectExceptionExactMessage_ accepts correct message.
     *
     * @param string $message
     *
     * @dataProvider exactMessageDataProvider
     */
    public function testExpectExceptionExactMessage(string $message): void
    {
        $this->expectExceptionExactMessage($message);

        throw new RuntimeException($message);
    }

    /**
     * Provides test data for testing method _expectExceptionExactMessage_.
     *
     * @return array [ [ message ], ... ]
     */
    public function exactMessageDataProvider(): array
    {
        return [
            'simple' => ['Error occurred'],
            'escaped characters' => ['\+*?[^]$(){}=!<>|:-#/']
        ];
    }
}
