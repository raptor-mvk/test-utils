<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\TestUtils\Exceptions\DataParseException;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class TestContainerWrapperDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _process_ throws _DataParseException_ with appropriate message.
     *
     * @param string $json
     * @param string $messageRegExp regular expression used to verify exception's message
     *
     * @dataProvider dataParseExceptionDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessage(string $json, string $messageRegExp): void
    {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        $dataProcessor = new TestContainerWrapperDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Provides test data to verify that _DataParseException_ is thrown.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    public function dataParseExceptionDataProvider(): array
    {
        return array_merge(
            $this->prepareNonUniqueNameTestData()
        );
    }

    /**
     * Prepares test data with non-unique values of service field _\_name_.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareNonUniqueNameTestData(): array
    {
        $topLevelJson = json_encode([['_name' => 'test1'], ['_name' => 'test1']]);
        $secondLevelJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test1']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test3']]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'not unique name, top level' => [$topLevelJson, '/^Non-unique test name test1 was found\.$/'],
            'not unique name, second level' => [$secondLevelJson, '/^Non-unique test name test1 was found\.$/'],
            'not unique name, third level' => [$thirdLevelJson, '/^Non-unique test name test3 was found\.$/']
        ];
    }

    /**
     * Checks that method _process_ returns array consists of instances of _TestContainer_.
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsResultWithTestContainersAsElements(): void
    {
        $testData = $this->prepareMultiResultTestJson();
        $dataProcessor = new TestContainerWrapperDataProcessor();

        $actual = $dataProcessor->process($testData);

        $message = 'All elements of resulting array should be instances of TestContainer';
        static::assertContainsOnly(TestDataContainer::class, $actual, false, $message);
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
            'multi_results' => $this->prepareMultiResultTestData()
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
            'test5' => ['top' => 'third', 'middle' => 'third', 'bottom' => 'third']
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

    /**
     * Checks that method _process_ returns correct result.
     *
     * @param string $json
     * @param array $expected
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsCorrectResult(string $json, array $expected): void
    {
        $dataProcessor = new TestContainerWrapperDataProcessor();

        $processed = $dataProcessor->process($json);

        $actual = [];
        foreach ($processed as $key => $value) {
            /** @var TestDataContainer $value */
            $actual[$key] = $value->allData();
        }
        static::assertArraysAreSame($expected, $actual);
    }
}
