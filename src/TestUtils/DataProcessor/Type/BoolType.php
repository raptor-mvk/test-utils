<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor\Type;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class BoolType extends AbstractType
{
    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
    /**
     * Returns _true_ if type is logical and _false_ otherwise.
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return true;
    }

    /**
     * Returns string representation of the type to be used in a generator.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'bool';
    }
}