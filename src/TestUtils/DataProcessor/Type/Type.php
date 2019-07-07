<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor\Type;

/**
 * Interface for field type.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface Type
{
    /**
     * Returns _true_ if type is logical and _false_ otherwise.
     *
     * @return bool
     */
    public function isBool(): bool;

    /**
     * Returns a type that can be used for variables that can be either of the current type or of a given type.
     *
     * @param Type $type
     *
     * @return Type
     */
    public function getCommonType(Type $type): Type;

    /**
     * Returns string representation of the type to be used in a generator.
     *
     * @return string
     */
    public function __toString(): string;
}