<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader\DataProcessor;

use JsonException;
use Raptor\Test\Exceptions\DataParseException;

/**
 * Обработчик тестовых данных в формате JSON.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class JSONDataProcessor implements DataProcessor
{
    /** @var string CHILDREN_KEY     ключ для служебного поля, содержащего дочерние элементы */
    private const CHILDREN_KEY = '_children';

    /** @var string NAME_KEY        ключ для служебного поля, содержащего наименование теста */
    private const NAME_KEY = '_name';

    /**
     * Обрабатывает элемент, являющийся объектом и содержащий дочерние элементы.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     *
     * @return array                    результирующий массив
     */
    private function processObjectElementWithChildren(array $element, string $level, ?array $default = null): array
    {
        if (isset($element[self::NAME_KEY])) {
            throw new DataParseException("Задано и наименование, и дочерние элементы на уровне $level");
        }
        $children = $element[self::CHILDREN_KEY];
        unset($element[self::CHILDREN_KEY]);
        return $this->processArrayElement($children, $level, array_merge($default ?? [], $element));
    }

    /**
     * Обрабатывает элемент, являющийся объектом и не содержащий дочерние элементы.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     *
     * @return array                    результирующий массив
     */
    private function processObjectElementWithoutChildren(array $element, string $level, ?array $default = null): array
    {
        $hasName = isset($element[self::NAME_KEY]);
        if (!$hasName) {
            throw new DataParseException("Не задано наименование теста на уровне $level");
        }
        $name = $element[self::NAME_KEY];
        if (!\is_string($name)) {
            throw new DataParseException("Наименование не является строкой на уровне $level");
        }
        if ($name === '') {
            throw new DataParseException("Пустое наименование на уровне $level");
        }
        $name = $element[self::NAME_KEY];
        unset($element[self::NAME_KEY]);
        return [$name => array_merge($default ?? [], $element)];
    }

    /**
     * Возвращает первое незнакомое сервисное поле из масссива наименований полей или null, если таких нет
     *
     * @param array     $fields     список полей элемента
     *
     * @return string|null          наименование первого незнакомого сервисного поля или null
     */
    private function hasUnknownServiceFields(array $fields): ?string
    {
        $result = null;
        foreach ($fields as $field) {
            if (($field !== self::NAME_KEY) && ($field !== self::CHILDREN_KEY) && ($field[0] === '_')) {
                $result = $field;
                break;
            }
        }
        return $result;
    }

    /**
     * Обрабатывает элемент, который должен быть объектом.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     *
     * @return array                    результирующий массив
     */
    private function processCommonObjectElement(array $element, string $level, ?array $default = null): array
    {
        $fields = array_keys($element);
        $unknownField = $this->hasUnknownServiceFields($fields);
        if ($unknownField !== null) {
            throw new DataParseException("Неизвестное служебное поле $unknownField на уровне $level");
        }
        $hasChildren = isset($element[self::CHILDREN_KEY]);
        return $hasChildren ? $this->processObjectElementWithChildren($element, $level, $default) :
            $this->processObjectElementWithoutChildren($element, $level, $default);
    }

    /**
     * Обрабатывает элемент, который должен быть массивом.
     *
     * @param array         $element    обрабатываемый элемент
     * @param string        $level      путь к элементу (для сообщения об ошибке)
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     *
     * @return array                    результирующий массив
     */
    private function processArrayElement(array $element, string $level, ?array $default = null): array
    {
        if ($element !== array_values($element)) {
            throw new DataParseException("Ожидается массив, получен объект на уровне $level");
        }
        $result = [];
        $i = 0;
        foreach ($element as $item) {
            $processedItems = $this->processCommonObjectElement($item, "$level.$i", $default);
            foreach ($processedItems as $name => $processedItem) {
                if (isset($result[$name])) {
                    throw new DataParseException("Обнаружено неуникальное наименование $name");
                }
                $result[$name] = $processedItem;
            }
            $i++;
        }
        return $result;
    }

    /**
     * Декодирует JSON-строку и возвращает данные в виде ассоциативного массива.
     *
     * @param string    $json       JSON-строка
     *
     * @return array                декодированные данные
     *
     * @throws DataParseException   ошибка обработки данных
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
     * Выполняет обработку тестовых данных.
     *
     * @param string    $data       строка с тестовыми данными
     *
     * @return array                преобразованные и обработанные данные
     *
     * @throws DataParseException   ошибка обработки данных
     */
    public function process(string $data): array
    {
        $decodedData = $this->decodeData($data);
        return $this->processArrayElement($decodedData, 'root');
    }
}
