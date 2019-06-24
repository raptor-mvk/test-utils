<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor;

/**
 * Processes JSON string with test data. Used to generate service file for IDE.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainerGeneratorDataProcessor extends AbstractJSONTestDataProcessor
{
    // constant values are used as return type hint in service file

    /** @var string INT_TYPE */
    public const INT_TYPE = 'int';

    /** @var string FLOAT_TYPE */
    public const FLOAT_TYPE = 'float';

    /** @var string STRING_TYPE */
    public const STRING_TYPE = 'string';

    /** @var string BOOL_TYPE */
    public const BOOL_TYPE = 'bool';

    /** @var string ARRAY_TYPE */
    public const ARRAY_TYPE = 'array';

    /** @var string MIXED_TYPE */
    public const MIXED_TYPE = 'mixed';

    /** @var string[] TYPE_MAP map used to convert gettype result to const */
    private const TYPE_MAP = [
        'boolean' => self::BOOL_TYPE,
        'integer' => self::INT_TYPE,
        'double' => self::FLOAT_TYPE,
        'string' => self::STRING_TYPE,
        'array' => self::ARRAY_TYPE,
        'NULL' => self::MIXED_TYPE
    ];

    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array $element
     * @param array|null $default array of default values passed from higher levels
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

    /**
     * Returns new type if current type does not coincide with processing type.
     *
     * @param string $currentType current type
     * @param string $checkedType processing type
     *
     * @return string
     */
    private function getNewType(string $checkedType, string $currentType): string
    {
        return ((($currentType === self::INT_TYPE) && ($checkedType === self::FLOAT_TYPE)) ||
            (($currentType === self::FLOAT_TYPE) && ($checkedType === self::INT_TYPE))) ? static::FLOAT_TYPE :
            static::MIXED_TYPE;
    }
}
