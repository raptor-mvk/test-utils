<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\DataProcessor\Type;

/**
 * Abstract base type.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * Returns _true_ if type is logical and _false_ otherwise.
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return false;
    }

    /**
     * Returns a type that can be used for variables that can be either of the current type or of a given type.
     *
     * @param TypeInterface $type
     *
     * @return TypeInterface
     */
    public function getCommonType(TypeInterface $type): TypeInterface
    {
        return ($type instanceof $this) ? $this : new MixedType();
    }
}
