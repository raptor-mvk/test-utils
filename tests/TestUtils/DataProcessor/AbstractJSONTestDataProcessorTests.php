<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use function is_array;
use Mockery;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\AbstractJSONTestDataProcessor;
use Raptor\TestUtils\ExtraAssertions;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class AbstractJSONTestDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _getProcessed_ returns _null_, if key has not been added previously.
     */
    public function testGetProcessedReturnsNullForNotAddedKey(): void
    {
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        $actual = static::invokeMethod($dataProcessor, 'getProcessed', ['some_key']);

        static::assertNull($actual, 'Method getProcessed should return null for non-existent key');
    }

    /**
     * Checks that both methods _getProcessed_ and _addProcessed_ work correctly.
     *
     * @param string $key
     * @param mixed $value
     *
     * @dataProvider addProcessedDataProvider
     */
    public function testGetProcessedAndAddProcessedWorkCorrectly(string $key, $value): void
    {
        /** @var AbstractJSONTestDataProcessor $dataProcessor */
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        static::invokeMethod($dataProcessor, 'addProcessed', [$key, $value]);
        $actual = static::invokeMethod($dataProcessor, 'getProcessed', [$key]);

        $assertion = is_array($value) ? 'assertArraysAreSame' : 'assertSame';
        $this->$assertion($value, $actual);
    }

    /**
     * Provides test data for testing method _addProcessed_.
     *
     * @return array [ [ key, value ], ... ]
     */
    public function addProcessedDataProvider(): array
    {
        return [
            'int' => ['int_key', 34],
            'float' => ['float_key', 436.22],
            'bool' => ['bool_key', true],
            'string' => ['string_key', 'very_long_string'],
            'array' => ['array_key', ['a' => 3, 'b' => 5]]
        ];
    }

    /**
     * Checks that method _hasProcessed_ returns _false_, if key has not been added previously.
     */
    public function testHasProcessedReturnsFalseForNotAddedKey(): void
    {
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();

        $actual = static::invokeMethod($dataProcessor, 'hasProcessed', ['some_key']);

        static::assertFalse($actual, 'Method hasProcessed should not return false for non-existent key');
    }

    /**
     * Checks that method _hasProcessed_ returns _true_, if key has already been added.
     */
    public function testHasProcessedReturnsTrueForAddedKey(): void
    {
        $key = 'some_key';
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class)->makePartial();
        static::invokeMethod($dataProcessor, 'addProcessed', [$key, 'value']);

        $actual = static::invokeMethod($dataProcessor, 'hasProcessed', [$key]);

        static::assertTrue($actual, 'Method hasProcessed should return true for existing key');
    }
}
