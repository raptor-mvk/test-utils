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
 * Класс с общими тестами для обработчиков тестовых данных в формате JSON.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class CommonTestContainerDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _process_ выбрасывает исключение _DataParseException_ с соответствующим сообщением.
     *
     * @param string    $dataProcessorClass    класс обработчика данных
     * @param string    $json                  входящий JSON
     * @param string    $messageRegExp         регулярное выражение для проверки сообщения об ошибке
     *
     * @dataProvider dataParseExceptionDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessage(
        string $dataProcessorClass,
        string $json,
        string $messageRegExp
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp($messageRegExp);

        /** @var DataProcessor $dataProcessor */
        $dataProcessor = new $dataProcessorClass();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные для проверки выбрасываемого исключения _DataParseException_.
     *
     * @return array    массив тестовых данных в формате [ [ dataProcessorClass, json, messageRegExp ], ... ]
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
     * Готовит тестовые данные для проверки выбрасываемого исключения _DataParseException_.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
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
     * Готовит тестовые данные для проверки синтаксической ошибки в JSON.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    private function prepareJsonSyntaxErrorTestData(): array
    {
        return ['json syntax error' => ['{"some_field":"some_value}', '/^JSON parse error\.$/']];
    }

    /**
     * Готовит тестовые данные с не-массивами там, где ожидается массив.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
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
            'not array, top level' => [$topLevelJson, '/^Expected array, object found at level root\.$/'],
            'not array, second level' => [$secondLevelJson, '/^Expected array, object found at level root.1\.$/'],
            'not array, third level' => [$thirdLevelJson, '/^Expected array, object found at level root.2.2\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с отсутствующими именами объектов, не содержащих поля _\_children_ на разных уровнях.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    private function prepareObjectWithoutNameTestData(): array
    {
        $topLevelJson = json_encode([['some_field' => 'some_value']]);
        $secondLevelJson =
            json_encode([['_children' => [['_name' => 'test'], ['other_field' => 'other_value', 'int' => 3]]]]);
        $thirdLevelChildren = [['_name' => 'test2'], ['_name' => 'test3'], ['string' => 'text', 'float' => 3.6]];
        $thirdLevelJson = json_encode([['_children' => [['_name' => 'name'], ['_children' => $thirdLevelChildren]]]]);
        return [
            'no name, top level' => [$topLevelJson, '/^Test name not found at level root.0\.$/'],
            'no name, second level' => [$secondLevelJson, '/^Test name not found at level root.0.1\.$/'],
            'no name, third level' => [$thirdLevelJson, '/^Test name not found at level root.0.1.2\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с не-строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    private function prepareNotStringNameTestData(): array
    {
        $intJson = json_encode([['_name' => 3]]);
        $boolJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => true]]]]);
        $floatChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 3.6]]]];
        $floatJson = json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $floatChildren]]);
        $arrayJson = json_encode([['_name' => ['some', 'name']]]);
        return [
            'int name on top level' => [$intJson, '/^Test name is not string at level root.0\.$/'],
            'bool name on second level' => [$boolJson, '/^Test name is not string at level root.1.0\.$/'],
            'float name on third level' => [$floatJson, '/^Test name is not string at level root.2.2.0\.$/'],
            'array name on top level' => [$arrayJson, '/^Test name is not string at level root.0\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с пустыми строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    private function prepareEmptyNameTestData(): array
    {
        $topLevelJson = json_encode([['_name' => '']]);
        $secondLevelJson = json_encode([['_name' => 'test1'], ['_children' => [['_name' => '']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => '']]]];
        $thirdLevelJson =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'empty name, top level' => [$topLevelJson, '/^Empty test name at level root.0\.$/'],
            'empty name, second level' => [$secondLevelJson, '/^Empty test name at level root.1.0\.$/'],
            'empty name, third level' => [$thirdLevelJson, '/^Empty test name at level root.2.2.0\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с неизвестными специальными полями.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
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
                [$topLevelJson, '/^Unknown service field _field at level root.0\.$/'],
            'unknown special field, second level' =>
                [$secondLevelJson, '/^Unknown service field _other at level root.1.0\.$/'],
            'unknown special field, third level' =>
                [$thirdLevelJson, '/^Unknown service field _unknown at level root.2.2.0\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с некорректными именами полей.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
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
                [$uppercaseJson, '/^Field name FIELD at level root.0 contains forbidden characters\.$/'],
            'special characters in field name' =>
                [$specialJson, '/^Field name !@#\$%\^&\*\(\) at level root.1.0 contains forbidden characters\.$/'],
            'spaces in field name' =>
                [$spacesJson, '/^Field name some field at level root.2.2.0 contains forbidden characters\.$/'],
            'digits in field name' =>
                [$digitJson, '/^Field name field163 at level root.0 contains forbidden characters\.$/']
        ];
    }

    /**
     * Готовит тестовые данные с наименованием и дочерними элементами в одном объекте.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
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
                [$topLevelJson, '/^Element at level root.0 contains both name and child elements\.$/'],
            'name and children, second level' =>
                [$secondLevelJson, '/^Element at level root.1.0 contains both name and child elements\.$/'],
            'name and children, third level' =>
                [$thirdLevelJson, '/^Element at level root.2.2.0 contains both name and child elements\.$/']
        ];
    }
}
