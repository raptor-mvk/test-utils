<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\TestContainerWrapperDataProcessor;
use Raptor\TestUtils\Exceptions\DataParseException;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;

/**
 * Класс с тестами для обработчика тестовых данных в формате JSON, используемый для загрузки данных в контейнеры
 * _TestContainerWrapperDataProcessor_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainerWrapperDataProcessorTests extends TestCase
{
    use ExtraAssertions;

    /**
     * Проверяет, что метод _process_ выбрасывает исключение _DataParseException_ с соответствующим сообщением.
     *
     * @param string    $json                  входящий JSON
     * @param string    $messageRegExp         регулярное выражение для проверки сообщения об ошибке
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
     * Предоставляет тестовые данные для проверки выбрасываемого исключения _DataParseException_.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    public function dataParseExceptionDataProvider(): array
    {
        return array_merge(
            $this->notUniqueNameDataProvider()
        );
    }

    /**
     * Предоставляет тестовые данные с неуникальными строковыми значениями в элементе для наименования.
     *
     * @return array    массив тестовых данных в формате [ [ json, messageRegExp ], ... ]
     */
    private function notUniqueNameDataProvider(): array
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
     * Проверяет, что метод _process_ возвращает массив, элементы которого являются экземплярами _TestContainer_.
     *
     * @dataProvider correctDataProvider
     */
    public function testProcessReturnsResultWithTestContainersAsElements(): void
    {
        $testData = $this->prepareMultiResultTestJson();
        $dataProcessor = new TestContainerWrapperDataProcessor();

        $actual = $dataProcessor->process($testData);

        $result = true;
        foreach ($actual as $value) {
            $result = $result && ($value instanceof TestDataContainer);
        }
        static::assertTrue($result, 'Все элементы результирующего массива должны быть экземплярами TestContainer');
    }

    /**
     * Предоставляет корректные тестовые данные для метода _process_.
     *
     * @return array    массив тестовых данных в формате [ [ json, expected ], ... ]
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
     * Готовит корректный тест, проверяющий установку значений по умолчанию из вышестоящих уровней.
     *
     * @return array    тестовый набор данных в формате [ json, expected ]
     */
    private function prepareDefaultValuesTestData(): array
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
     * Готовит корректный тест, возвращающий несколько тестовых наборов на разных уровнях.
     *
     * @return array    тестовый набор данных в формате [ json, expected ]
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
     * Готовит входящие данные для теста, возвращающего несколько тестовых наборов на разных уровнях.
     *
     * @return string    входящие данные для теста
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
     * Проверяет, что метод _process_ возвращает коректный результат.
     *
     * @param string    $json        JSON-строка для обработки
     * @param array     $expected    ожидаемый результат
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
