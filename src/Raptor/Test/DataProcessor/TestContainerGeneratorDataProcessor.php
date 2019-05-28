<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

/**
 * Обработчик тестовых данных в формате JSON, используемый для генератора вспомогательного файла для IDE.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainerGeneratorDataProcessor extends AbstractJSONTestDataProcessor
{
    /** @var string    INT_TYPE    наименование целочисленного типа */
    public const INT_TYPE = 'int';

    /** @var string    FLOAT_TYPE    наименование вещественного типа */
    public const FLOAT_TYPE = 'float';

    /** @var string    STRING_TYPE    наименование строкового типа */
    public const STRING_TYPE = 'string';

    /** @var string    BOOL_TYPE    наименование логического типа */
    public const BOOL_TYPE = 'bool';

    /** @var string    ARRAY_TYPE    наименование типа-массива */
    public const ARRAY_TYPE = 'array';

    /** @var string    MIXED_TYPE    наименование смешанного типа */
    public const MIXED_TYPE = 'mixed';

    /** @var string[]    TYPE_MAP    массив соответствий типов из gettype типам значений */
    private const TYPE_MAP = [
        'boolean' => self::BOOL_TYPE,
        'integer' => self::INT_TYPE,
        'double' => self::FLOAT_TYPE,
        'string' => self::STRING_TYPE,
        'array' => self::ARRAY_TYPE,
        'NULL' => self::MIXED_TYPE
    ];

    /**
     * Получает новый тип, если текущий тип не совпадает с обрабатываемым.
     *
     * @param string    $currentType    текущий тип
     * @param string    $checkedType    обрабатываемый тип
     *
     * @return string    новый тип
     */
    private function getNewType(string $checkedType, string $currentType): string
    {
        return ((($currentType === self::INT_TYPE) && ($checkedType === self::FLOAT_TYPE)) ||
            (($currentType === self::FLOAT_TYPE) && ($checkedType === self::INT_TYPE))) ? static::FLOAT_TYPE :
            static::MIXED_TYPE;
    }

    /**
     * Обрабатывает тестовый набор данных (элемент, содержащий служебное поле _name), предполагая, что он корректен
     * (без обработки ошибок).
     *
     * @param array $element обрабатываемый элемент
     * @param array|null $default значения по умолчанию с вышестоящих уровней
     */
    protected function processTestCase(array $element, ?array $default = null): void
    {
        unset($element[static::NAME_KEY]);
        foreach ($element as $field => $value) {
            /** @var string $type */
            $type = gettype($value);
            $checkedType = static::TYPE_MAP[$type] ?? null;
            if ($checkedType !== null) {
                /** @var string $currentType */
                $currentType = $this->getProcessed($field);
                $newType = (($currentType === null) || ($currentType === $checkedType)) ?
                    $checkedType : $this->getNewType($checkedType, $currentType);
                $this->addProcessed($field, $newType);
            }
        }
    }
}
