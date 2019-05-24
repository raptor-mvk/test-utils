<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\DataProcessor\JSONDataProcessor;
use Raptor\Test\Exceptions\DataParseException;
use Raptor\Test\ExtraAssertions;

/**
 * Класс с тестами для обработчика файла данных в формате JSON `JSONDataProcessor`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class JSONDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о синтаксической ошибке
     * при ошибке разбора JSON-данных.
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageOnJSONSyntaxError(): void
    {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp('/^Ошибка при разборе JSON-данных$/');

        $data = '[{"some_field}]';
        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($data);
    }

    /**
     * Предоставляет тестовые данные с не-массивами там, где ожидается массив.
     *
     * @return array    массив тестовых данных в формате [ [ json, level ], ... ]
     */
    public function notArrayDataProvider(): array
    {
        $topLevelString = json_encode(['some_field' => 'some_value']);
        $secondLevelString =
            json_encode([['_name' => 'test1'], ['_children' => ['other_field' => 'other_value', 'int' => 3]]]);
        $thirdLevelChildren =
            [['_name' => 'test4'], ['_name' => 'test5'], ['_children' => ['string' => 'text', 'float' => 3.6]]];
        $thirdLevelString =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'top level' => [$topLevelString, 'root'],
            'second level' => [$secondLevelString, 'root.1'],
            'third level' => [$thirdLevelString, 'root.2.2']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о том, что ожидается
     * массив, если на верхнем уровне или в элементе _\_children_ не массив.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider notArrayDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenRootObjectIsNotArray(
        string $json,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Ожидается массив, получен объект на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с отсутствующими именами объектов, не содержащих поля _\_children_ на разных
     * уровнях.
     *
     * @return array    массив тестовых данных в формате [ [ json, level ], ... ]
     */
    public function objectWithoutNameDataProvider(): array
    {
        $topLevelString = json_encode([['some_field' => 'some_value']]);
        $secondLevelString =
            json_encode([['_children' => [['_name' => 'test'], ['other_field' => 'other_value', 'int' => 3]]]]);
        $thirdLevelChildren = [['_name' => 'test2'], ['_name' => 'test3'], ['string' => 'text', 'float' => 3.6]];
        $thirdLevelString = json_encode([['_children' => [['_name' => 'name'], ['_children' => $thirdLevelChildren]]]]);
        return [
            'top level' => [$topLevelString, 'root.0'],
            'second level' => [$secondLevelString, 'root.0.1'],
            'third level' => [$thirdLevelString, 'root.0.1.2']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением об отсутствии имени
     * теста, если для объекта не заданы ни служебное поле _\_name_, ни служебное поле _\_children_.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider objectWithoutNameDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenObjectHasNoServiceFields(
        string $json,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Не задано наименование теста на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с не-строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, level ], ... ]
     */
    public function notStringDataProvider(): array
    {
        $intString = json_encode([['_name' => 3]]);
        $boolString = json_encode([['_name' => 'test1'], ['_children' => [['_name' => true]]]]);
        $floatChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 3.6]]]];
        $floatString = json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $floatChildren]]);
        $arrayString = json_encode([['_name' => ['some', 'name']]]);
        return [
            'int on top level' => [$intString, 'root.0'],
            'bool on second level' => [$boolString, 'root.1.0'],
            'float on third level' => [$floatString, 'root.2.2.0'],
            'array on top level' => [$arrayString, 'root.0']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о том, что имя теста не
     * является строкой, если элемент _\_name_ не является строкой.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider notStringDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenNameObjectIsNotString(
        string $json,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Наименование не является строкой на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с пустыми строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, level ], ... ]
     */
    public function emptyNameDataProvider(): array
    {
        $topLevelString = json_encode([['_name' => '']]);
        $secondLevelString = json_encode([['_name' => 'test1'], ['_children' => [['_name' => '']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => '']]]];
        $thirdLevelString =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'top level' => [$topLevelString, 'root.0'],
            'second level' => [$secondLevelString, 'root.1.0'],
            'third level' => [$thirdLevelString, 'root.2.2.0']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о том, что имя теста
     * пустое, если элемент _\_name_ содержит пустую строку.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider emptyNameDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenNameIsEmpty(
        string $json,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Пустое наименование на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с неуникальными строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, name ], ... ]
     */
    public function notUniqueNameDataProvider(): array
    {
        $topLevelString = json_encode([['_name' => 'test1'], ['_name' => 'test1']]);
        $secondLevelString = json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test1']]]]);
        $thirdLevelChildren = [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test3']]]];
        $thirdLevelString =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'top level' => [$topLevelString, 'test1'],
            'second level' => [$secondLevelString, 'test1'],
            'third level' => [$thirdLevelString, 'test3']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о том, что имя теста не
     * уникальное, если элемент _\_name_ содержит строку, которая ранее уже присутствовала в другом элементе _\_name_.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $name   наименование для сообщения об ошибке
     *
     * @dataProvider notUniqueNameDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenNameIsNotUnique(
        string $json,
        string $name
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Обнаружено неуникальное наименование $name$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с неизвестными специальными полями.
     *
     * @return array    массив тестовых данных в формате [ [ json, field, level ], ... ]
     */
    public function unknownSpecialFieldDataProvider(): array
    {
        $topLevelString = json_encode([['_name' => 'name', '_field' => 3]]);
        $secondLevelString = json_encode([['_name' => 'test1'], ['_children' => [['name' => 'test2', '_other' => 6]]]]);
        $thirdLevelChildren =
            [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => [['_name' => 'test6', '_unknown' => 55]]]];
        $thirdLevelString =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'top level' => [$topLevelString, '_field', 'root.0'],
            'second level' => [$secondLevelString, '_other', 'root.1.0'],
            'third level' => [$thirdLevelString, '_unknown', 'root.2.2.0']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о неизвестных служебных
     * полях, если в JSON-данных присутствуют поля, наименование которых имеет префикс _.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $field  наименование служебного поля для сообщения об ошибке
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider unknownSpecialFieldDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenExistsUnknownSpecialField(
        string $json,
        string $field,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Неизвестное служебное поле $field на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Предоставляет тестовые данные с наименованием и дочерними элементами в одном объекте.
     *
     * @return array    массив тестовых данных в формате [ [ json, level ], ... ]
     */
    public function nameAndChildrenDataProvider(): array
    {
        $topLevelString = json_encode([['_name' => 'name', '_children' => [['id' => 1]]]]);
        $secondLevelString =
            json_encode([['_name' => 'test1'], ['_children' => [['_name' => 'test2', '_children' => [['id' => 6]]]]]]);
        $thirdLevelGrandchildren = [['_name' => 'test7', '_children' => [['id' => 55]]]];
        $thirdLevelChildren =
            [['_name' => 'test5'], ['_name' => 'test6'], ['_children' => $thirdLevelGrandchildren]];
        $thirdLevelString =
            json_encode([['_name' => 'test2'], ['_name' => 'test3'], ['_children' => $thirdLevelChildren]]);
        return [
            'top level' => [$topLevelString, 'root.0'],
            'second level' => [$secondLevelString, 'root.1.0'],
            'third level' => [$thirdLevelString, 'root.2.2.0']
        ];
    }

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о неизвестных служебных
     * полях, если в JSON-данных присутствуют поля, наименование которых имеет префикс _.
     *
     * @param string    $json   JSON-строка для обработки
     * @param string    $level  уровень для сообщения об ошибке
     *
     * @dataProvider nameAndChildrenDataProvider
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageWhenNameAndChildrenInSameObject(
        string $json,
        string $level
    ): void {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp("/^Задано и наименование, и дочерние элементы на уровне $level$/");

        $dataProcessor = new JSONDataProcessor();

        $dataProcessor->process($json);
    }

    /**
     * Готовит корректный тест, проверяющий установку значений по умолчанию из вышестоящих уровней.
     *
     * @return array    тестовый набор данных в формате [ json, expected ]
     */
    private function prepareDefaultValuesTest(): array
    {
        $defaultValuesChildren = [['middle' => 'middle', '_children' => [['bottom' => 'bottom', '_name' => 'test1']]]];
        $defaultValuesData = [['top' => 'top', '_children' => $defaultValuesChildren]];
        $defaultValuesExpected = ['test1' => ['top' => 'top', 'middle' => 'middle', 'bottom' => 'bottom']];
        return [json_encode($defaultValuesData), $defaultValuesExpected];
    }

    /**
     * Готовит корректный тест, проверяющий замену значений по умолчанию из вышестоящих уровней.
     *
     * @return array    тестовый набор данных в формате [ json, expected ]
     */
    private function prepareOverriddenValuesTest(): array
    {
        $overriddenGrandchildren =
            [['bottom' => 'bottom', 'middle' => 'bottom', 'top' => 'bottom', '_name' => 'test1']];
        $overriddenValuesData =
            [['top' => 'top', '_children' => [['middle' => 'middle', '_children' => $overriddenGrandchildren]]]];
        $overriddenValuesExpected = ['test1' => ['top' => 'bottom', 'middle' => 'bottom', 'bottom' => 'bottom']];
        return [json_encode($overriddenValuesData), $overriddenValuesExpected];
    }

    /**
     * Готовит корректный тест, возвращающий несколько тестовых наборов на разных уровнях.
     *
     * @return array    тестовый набор данных в формате [ json, expected ]
     */
    private function prepareMultiResultTest(): array
    {
        $firstDataChildren = [['_name' => 'test1', 'middle' => 'value1'], ['_name' => 'test2', 'middle' => 'value2']];
        $firstData = [['top' => 'first', 'bottom' => 'first', '_children' => $firstDataChildren]];
        $secondDataGrandchildren =
            [['_name' => 'test3', 'bottom' => 'value3'], ['_name' => 'test4', 'bottom' => 'value4']];
        $secondData =
            [['top' => 'second', '_children' => [['middle' => 'second', '_children' => $secondDataGrandchildren]]]];
        $thirdData = [['top' => 'third', 'middle' => 'third', 'bottom' => 'third', '_name' => 'test5']];
        $expected = [
            'test1' => ['top' => 'first', 'bottom' => 'first', 'middle' => 'value1'],
            'test2' => ['top' => 'first', 'bottom' => 'first', 'middle' => 'value2'],
            'test3' => ['top' => 'second', 'middle' => 'second', 'bottom' => 'value3'],
            'test4' => ['top' => 'second', 'middle' => 'second', 'bottom' => 'value4'],
            'test5' => ['top' => 'third', 'middle' => 'third', 'bottom' => 'third']
        ];
        return [json_encode(array_merge($firstData, $secondData, $thirdData)), $expected];
    }

    /**
     * Предоставляет корректные тестовые данные для метода _process_.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
     */
    public function correctDataProvider(): array
    {
        return [
            'default values' => $this->prepareDefaultValuesTest(),
            'overridden_values' => $this->prepareOverriddenValuesTest(),
            'multi_results' => $this->prepareMultiResultTest()
        ];
    }

    /**
     * Проверяет, что метод _process_ возвращает коректный результат.
     *
     * @param string    $json       JSON-строка для обработки
     * @param array     $expected   ожидаемый результат
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsCorrectResult(string $json, array $expected): void
    {
        $dataProcessor = new JSONDataProcessor();

        $actual = $dataProcessor->process($json);
        static::assertArraysAreSame($expected, $actual);
    }
}
