<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\DataProcessor;
use Raptor\TestUtils\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\TestUtils\Exceptions\DataParseException;
use Raptor\TestUtils\ExtraAssertions;

/**
 * Common tests for all JSON string data processors.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class CommonTestContainerDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Checks that method _process_ throws _DataParseException_ with corresponding message.
     *
     * @param string $dataProcessorClass
     * @param string $json
     * @param string $messageRegExp regular expression used to validate exception's message
     *
     * @dataProvider dataParseExceptionDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessage(
        string $dataProcessorClass,
        string $json,
        string $expectedMessage
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        /** @var DataProcessor $dataProcessor */
        $dataProcessor = new $dataProcessorClass();

        $dataProcessor->process($json);
    }

    /**
     * Provides test data to verify that _DataParseException_ is thrown.
     *
     * @return array [ [ dataProcessorClass, json, messageRegExp ], ... ]
     */
    public function dataParseExceptionDataProvider(): array
    {
        $testCases = $this->prepareDataParseExceptionTestData();
        $dataProcessors = [
            'wrapper' => TestContainerWrapperDataProcessor::class,
            'generator' => TestContainerGeneratorDataProcessor::class
        ];
        $result = [];
        foreach ($testCases as $testName => $testData) {
            foreach ($dataProcessors as $processorName => $processorClass) {
                $result["$processorName, $testName"] = array_merge([$processorClass], $testData);
            }
        }
        return $result;
    }

    /**
     * Prepares test data to verify that _DataParseException_ is thrown.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareDataParseExceptionTestData(): array
    {
        return array_merge(
            $this->prepareJsonSyntaxErrorTestData(),
            $this->prepareNotArrayTestData(),
            $this->prepareObjectWithoutNameTestData(),
            $this->prepareNotStringNameTestData(),
            $this->prepareEmptyNameTestData(),
            $this->prepareUnknownSpecialFieldTestData(),
            $this->prepareIncorrectFieldNameTestData(),
            $this->prepareNameAndChildrenTestData()
        );
    }

    /**
     * Prepares test data to verify JSON syntax error.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareJsonSyntaxErrorTestData(): array
    {
        return ['json syntax error' => ['{"some_field":"some_value}', 'JSON parse error.']];
    }

    /**
     * Prepares test data with JSON objects instead of JSON arrays.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareNotArrayTestData(): array
    {
        $topLevelJson = json_encode(['some_field' => 'some_value']);
        $secondLevelJson =
            json_encode([['_name' => 'test1'], ['_children' => ['other_field' => 'other_value', 'int' => 3]]]);
        $thirdLevelChildren =
            [['_name' => 'test4'], ['_name' => 'test5'], ['_children' => ['string' => 'text', 'float' => 3.6]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'not array, top level' => [$topLevelJson, 'Expected array, object found at level root.'],
            'not array, second level' => [$secondLevelJson, 'Expected array, object found at level root.1.'],
            'not array, third level' => [$thirdLevelJson, 'Expected array, object found at level root.2.2.']
        ];
    }

    /**
     * Prepares test data with JSON objects without _\_name_ and _\_children_ service fields.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareObjectWithoutNameTestData(): array
    {
        $topLevelJson = json_encode([['some_field' => 'some_value']]);
        $secondLevelJson =
            json_encode([['_children' => [['_name' => 'test'], ['other_field' => 'other_value', 'int' => 3]]]]);
        $thirdLevelChildren = [['_name' => 'test2'], ['_name' => 'test3'], ['string' => 'text', 'float' => 3.6]];
        $thirdLevelJson = json_encode([['_children' => [['_name' => 'name'], ['_children' => $thirdLevelChildren]]]]);
        return [
            'no name, top level' => [$topLevelJson, 'Test name not found at level root.0.'],
            'no name, second level' => [$secondLevelJson, 'Test name not found at level root.0.1.'],
            'no name, third level' => [$thirdLevelJson, 'Test name not found at level root.0.1.2.']
        ];
    }

    /**
     * Prepares test data with non-string values of the service field _\_name_.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareNotStringNameTestData(): array
    {
        $intJson = json_encode([['_name' => 3]]);
        $boolJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => true]]]]);
        $floatChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 3.6]]]];
        $floatJson = json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $floatChildren]]);
        $arrayJson = json_encode([['_name' => ['some', 'name']]]);
        return [
            'int name on top level' => [$intJson, 'Test name is not string at level root.0.'],
            'bool name on second level' => [$boolJson, 'Test name is not string at level root.1.0.'],
            'float name on third level' => [$floatJson, 'Test name is not string at level root.2.2.0.'],
            'array name on top level' => [$arrayJson, 'Test name is not string at level root.0.']
        ];
    }

    /**
     * Prepares test data with empty values of the service field _\_name_.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareEmptyNameTestData(): array
    {
        $topLevelJson = json_encode([['_name' => '']]);
        $secondLevelJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => '']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => '']]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'empty name, top level' => [$topLevelJson, 'Empty test name at level root.0.'],
            'empty name, second level' => [$secondLevelJson, 'Empty test name at level root.1.0.'],
            'empty name, third level' => [$thirdLevelJson, 'Empty test name at level root.2.2.0.']
        ];
    }

    /**
     * Prepares test data with unknown service fields.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareUnknownSpecialFieldTestData(): array
    {
        $topLevelJson = json_encode([['_name' => 'name', '_field' => 3]]);
        $secondLevelJson =
            json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test2', '_other' => 6]]]]);
        $thirdLevelChildren =
            [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test6', '_unknown' => 55]]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'unknown special field, top level' =>
                [$topLevelJson, 'Unknown service field _field at level root.0.'],
            'unknown special field, second level' =>
                [$secondLevelJson, 'Unknown service field _other at level root.1.0.'],
            'unknown special field, third level' =>
                [$thirdLevelJson, 'Unknown service field _unknown at level root.2.2.0.']
        ];
    }

    /**
     * Prepares test data with incorrect field names.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareIncorrectFieldNameTestData(): array
    {
        $uppercaseJson = json_encode([['_name' => 'name', 'FIELD' => 3]]);
        $specialJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test2', '!@#$%^&*()' => 6]]]]);
        $spaceChildren =
            [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test6', 'some field' => 55]]]];
        $spacesJson = json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $spaceChildren]]);
        $digitJson = json_encode([['_name' => 'name', 'field163' => 3]]);
        return [
            'uppercase letters in field name' =>
                [$uppercaseJson, 'Field name FIELD at level root.0 contains forbidden characters.'],
            'special characters in field name' =>
                [$specialJson, 'Field name !@#$%^&*() at level root.1.0 contains forbidden characters.'],
            'spaces in field name' =>
                [$spacesJson, 'Field name some field at level root.2.2.0 contains forbidden characters.'],
            'digits in field name' =>
                [$digitJson, 'Field name field163 at level root.0 contains forbidden characters.']
        ];
    }

    /**
     * Prepares test data with both _\_name_ and _\_children_ service fields filled.
     *
     * @return array [ [ json, messageRegExp ], ... ]
     */
    private function prepareNameAndChildrenTestData(): array
    {
        $topLevelJson = json_encode([['_name' => 'name', '_children' => [['id' => 1]]]]);
        $secondLevelJson =
            json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test2', '_children' => [['id' => 6]]]]]]);
        $thirdLevelGrandchildren = [['_name' => 'test7', '_children' => [['id' => 55]]]];
        $thirdLevelChildren =
            [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => $thirdLevelGrandchildren]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'name and children, top level' =>
                [$topLevelJson, 'Element at level root.0 contains both name and child elements.'],
            'name and children, second level' =>
                [$secondLevelJson, 'Element at level root.1.0 contains both name and child elements.'],
            'name and children, third level' =>
                [$thirdLevelJson, 'Element at level root.2.2.0 contains both name and child elements.']
        ];
    }
}
