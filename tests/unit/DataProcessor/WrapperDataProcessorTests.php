<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\WrapperDataProcessor;
use Raptor\TestUtils\Exceptions\DataParseException;
use Raptor\TestUtils\ExtraAssertionsTrait;
use Raptor\TestUtils\ExtraUtilsTrait;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;
use function is_array;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 * @author Igor Vodka
 *
 * @copyright 2019, raptor_MVK
 */
final class WrapperDataProcessorTests extends TestCase
{
    use ExtraAssertionsTrait, ExtraUtilsTrait;

    /**
     * Checks that method _process_ throws _DataParseException_ with appropriate message.
     *
     * @param string $json
     * @param string $message
     *
     * @dataProvider dataParseExceptionDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessage(string $json, string $message): void
    {
        $this->expectException(DataParseException::class);
        $this->expectExceptionExactMessage($message);

        $dataProcessor = new WrapperDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Provides test data to verify that _DataParseException_ is thrown.
     *
     * @return array [ [ json, message ], ... ]
     */
    public function dataParseExceptionDataProvider(): array
    {
        return array_merge(
            $this->prepareNonUniqueNameTestData()
        );
    }

    /**
     * Checks that method _process_ returns array of arrays, each of which contains single TestDataContainer instance.
     *
     * @param string $json
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsArrayOfArraysContainingSingleTestDataContainerInstance(string $json): void
    {
        $dataProcessor = new WrapperDataProcessor();

        $processed = $dataProcessor->process($json);

        $result = true;
        foreach ($processed as $value) {
            $result = $result && is_array($value) && (1 === count($value)) &&
                ($value[0] instanceof TestDataContainer);
        }
        static::assertTrue($result, 'Process should return array of arrays with single element');
    }

    /**
     * Provides correct test data for testing method _process_.
     *
     * @return array [ [ json, expected ], ... ]
     */
    public function correctDataProvider(): array
    {
        return [
            'default values' => $this->prepareDefaultValuesTestData(),
            'overridden_values' => $this->prepareOverriddenValuesTestData(),
            'multi_results' => $this->prepareMultiResultTestData(),
        ];
    }

    /**
     * Checks that method _process_ returns correct result.
     *
     * @param string $json
     * @param array  $expected
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsCorrectResult(string $json, array $expected): void
    {
        $dataProcessor = new WrapperDataProcessor();

        $processed = $dataProcessor->process($json);

        $actual = [];
        foreach ($processed as $key => $value) {
            /** @var array $value */
            $actual[$key] = $value[0]->allData();
        }
        static::assertArraysAreSame($expected, $actual);
    }

    /**
     * Prepares test data with non-unique values of service field _\_name_.
     *
     * @return array [ [ json, message ], ... ]
     */
    private function prepareNonUniqueNameTestData(): array
    {
        $topLevelJson = json_encode([['_name' => 'test1'], ['_name' => 'test1']]);
        $secondLevelJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test1']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test3']]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);

        return [
            'not unique name, top level' => [$topLevelJson, 'Non-unique test name test1 was found.'],
            'not unique name, second level' => [$secondLevelJson, 'Non-unique test name test1 was found.'],
            'not unique name, third level' => [$thirdLevelJson, 'Non-unique test name test3 was found.'],
        ];
    }

    /**
     * Prepares correct test data to verify that default values correctly pass from higher levels.
     *
     * @return array [ [ json, expected ], ... ]
     */
    private function prepareDefaultValuesTestData(): array
    {
        $defaultValuesChildren = [['middle' => 'middle', '_children' => [['bottom' => 'bottom', '_name' => 'test1']]]];
        $defaultValuesData = [['top' => 'top', '_children' => $defaultValuesChildren]];
        $defaultValuesExpected = ['test1' => ['top' => 'top', 'middle' => 'middle', 'bottom' => 'bottom']];

        return [json_encode($defaultValuesData), $defaultValuesExpected];
    }

    /**
     * Prepares correct test data to verify that default values correctly overridden by values from lower levels.
     *
     * @return array [ [ json, expected ], ... ]
     */
    private function prepareOverriddenValuesTestData(): array
    {
        $overriddenGrandchildren =
            [['bottom' => 'bottom', 'middle' => 'bottom', 'top' => 'bottom', '_name' => 'test1']];
        $overriddenValuesData =
            [['top' => 'top', '_children' => [['middle' => 'middle', '_children' => $overriddenGrandchildren]]]];
        $overriddenValuesExpected = ['test1' => ['top' => 'bottom', 'middle' => 'bottom', 'bottom' => 'bottom']];

        return [json_encode($overriddenValuesData), $overriddenValuesExpected];
    }

    /**
     * Prepares correct test data that returns several test cases at different levels.
     *
     * @return array [ [ json, expected ], ... ]
     */
    private function prepareMultiResultTestData(): array
    {
        $testData = $this->prepareMultiResultTestJson();
        $expected = [
            'test1' => ['top' => 'first', 'bottom' => 'first', 'middle' => 'value1'],
            'test2' => ['top' => 'first', 'bottom' => 'first', 'middle' => 'value2'],
            'test3' => ['top' => 'second', 'middle' => 'second', 'bottom' => 'value3'],
            'test4' => ['top' => 'second', 'middle' => 'second', 'bottom' => 'value4'],
            'test5' => ['top' => 'third', 'middle' => 'third', 'bottom' => 'third'],
        ];

        return [$testData, $expected];
    }

    /**
     * Prepares JSON input string that should be processed as several test cases at different levels.
     *
     * @return string
     */
    private function prepareMultiResultTestJson(): string
    {
        $firstDataChildren = [['_name' => 'test1', 'middle' => 'value1'], ['_name' => 'test2', 'middle' => 'value2']];
        $firstData = [['top' => 'first', 'bottom' => 'first', '_children' => $firstDataChildren]];
        $secondDataGrandchildren =
            [['_name' => 'test3', 'bottom' => 'value3'], ['_name' => 'test4', 'bottom' => 'value4']];
        $secondData =
            [['top' => 'second', '_children' => [['middle' => 'second', '_children' => $secondDataGrandchildren]]]];
        $thirdData = [['top' => 'third', 'middle' => 'third', 'bottom' => 'third', '_name' => 'test5']];

        return json_encode(array_merge($firstData, $secondData, $thirdData));
    }
}
