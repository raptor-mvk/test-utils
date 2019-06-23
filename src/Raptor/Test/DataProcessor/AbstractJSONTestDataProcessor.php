<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

use function is_string;
use JsonException;
use Raptor\Test\Exceptions\DataParseException;

/**
 * Абстрактный класс обработчика JSON-файлов, содержащих тестовые данные.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class AbstractJSONTestDataProcessor implements DataProcessor
{
    /** @var string    CHILDREN_KEY    ключ для служебного поля, содержащего дочерние элементы */
    protected const CHILDREN_KEY = '_children';

    /** @var string    NAME_KEY    ключ для служебного поля, содержащего наименование теста */
    protected const NAME_KEY = '_name';

    /** @var array    $processedData    обработанные данные */
    private $processedData;

    /**
     * Выполняет обработку тестовых данных.
     *
     * @param string    $data    строка с тестовыми данными
     *
     * @return array    преобразованные и обработанные данные
     *
     * @throws DataParseException    ошибка обработки данных
     */
    public function process(string $data): array
    {
        $decodedData = $this->decodeData($data);
        $this->processedData = [];
        $this->processArrayElement($decodedData, 'root');
        return $this->processedData;
    }

    /**
     * Декодирует JSON-строку и возвращает данные в виде ассоциативного массива.
     *
     * @param string    $json    JSON-строка
     *
     * @return array    декодированные данные
     *
     * @throws DataParseException    ошибка обработки данных
     */
    private function decodeData(string $json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } /** @noinspection PhpRedundantCatchClauseInspection __approved__ */ catch (JsonException $e) {
            /** JsonException может быть выброшено */
            throw new DataParseException('Ошибка при разборе JSON-данных', 0, $e);
        }
    }

    /**
     * Обрабатывает элемент, который должен быть массивом.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    private function processArrayElement(array $element, string $level, ?array $default = null): void
    {
        if ($element !== array_values($element)) {
            throw new DataParseException("Ожидается массив, получен объект на уровне $level");
        }
        $i = 0;
        foreach ($element as $item) {
            $this->processCommonObjectElement($item, "$level.$i", $default);
            $i++;
        }
    }

    /**
     * Обрабатывает элемент, который должен быть объектом.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    private function processCommonObjectElement(array $element, string $level, ?array $default = null): void
    {
        $fields = array_keys($element);
        $this->checkFields($fields, $level);
        $hasChildren = isset($element[self::CHILDREN_KEY]);
        if ($hasChildren) {
            $this->processObjectElementWithChildren($element, $level, $default);
            return;
        }
        $this->processObjectElementWithoutChildren($element, $level, $default);
    }

    /**
     * Проверяет, что массив наименований полей не содержит некорретных наименований полей или незнакомых сервисных
     * полей, если это не так, то выбрасывает исключение.
     *
     * @param array     $fields    список полей элемента
     * @param string    $level     путь к элементу (для сообщения об ошибке)
     *
     * @throws DataParseException    найдено неизвестное служебное поле или поле с некорректным наименованием
     */
    private function checkFields(array $fields, string $level): void
    {
        foreach ($fields as $field) {
            if (($field !== self::NAME_KEY) && ($field !== self::CHILDREN_KEY) && ($field[0] === '_')) {
                throw new DataParseException("Неизвестное служебное поле $field на уровне $level");
            }
            if (preg_match('/[^a-z_]/', $field) !== 0) {
                throw new DataParseException("Наименование поля $field на уровне $level содержит запрещённые символы");
            }
        }
    }

    /**
     * Обрабатывает элемент, являющийся объектом и содержащий дочерние элементы.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    private function processObjectElementWithChildren(array $element, string $level, ?array $default = null): void
    {
        if (isset($element[self::NAME_KEY])) {
            throw new DataParseException("Задано и наименование, и дочерние элементы на уровне $level");
        }
        $children = $element[self::CHILDREN_KEY];
        unset($element[self::CHILDREN_KEY]);
        $this->processArrayElement($children, $level, array_merge($default ?? [], $element));
    }

    /**
     * Обрабатывает элемент, являющийся объектом и не содержащий дочерние элементы.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    private function processObjectElementWithoutChildren(array $element, string $level, ?array $default = null): void
    {
        $hasName = isset($element[self::NAME_KEY]);
        if (!$hasName) {
            throw new DataParseException("Не задано наименование теста на уровне $level");
        }
        $name = $element[self::NAME_KEY];
        if (!is_string($name)) {
            throw new DataParseException("Наименование не является строкой на уровне $level");
        }
        if ($name === '') {
            throw new DataParseException("Пустое наименование на уровне $level");
        }
        $this->processTestCase($element, $default);
    }

    /**
     * Добавляет в результат обработки новый элемент с заданными ключом.
     *
     * @param string    $key     ключ
     * @param mixed     $data    данные
     */
    protected function addProcessed(string $key, $data): void
    {
        $this->processedData[$key] = $data;
    }

    /**
     * Проверяет, что в результате обработки есть элемент с заданным ключом.
     *
     * @param string    $key    ключ
     *
     * @return bool    _true_, если элемент с заданным ключом есть в результате обработки, _false_ иначе
     */
    protected function hasProcessed(string $key): bool
    {
        return isset($this->processedData[$key]);
    }

    /**
     * Возвращает элемент с заданными ключом из результата обработки.
     *
     * @param string    $key    ключ
     *
     * @return mixed|null    значение элемента с заданным ключом
     */
    protected function getProcessed(string $key)
    {
        return $this->processedData[$key] ?? null;
    }

    /**
     * Обрабатывает тестовый набор данных (элемент, содержащий служебное поле _name), предполагая, что он корректен
     * (без обработки ошибок).
     *
     * @param array         $element    обрабатываемый элемент
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    abstract protected function processTestCase(array $element, ?array $default = null): void;
}
