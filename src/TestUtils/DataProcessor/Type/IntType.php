<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor\Type;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class IntType extends AbstractType
{
    /**
     * Returns string representation of the type to be used in a generator.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'int';
    }

    /**
     * Returns a type that can be used for variables that can be either of the current type or of a given type.
     *
     * @param Type $type
     *
     * @return Type
     */
    public function getCommonType(Type $type): Type
    {
        return ($type instanceof FloatType) ? $type : parent::getCommonType($type);
    }
}
