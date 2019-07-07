<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor\TypeFactory;

use Raptor\TestUtils\DataProcessor\Type\Type;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface TypeFactory
{
    /**
     * Creates Type corresponding to _gettype_ result.
     *
     * @param string|null $typeValue result of _gettype_ called on variable
     *
     * @return Type
     */
    public function createType(?string $typeValue = null): Type;

    /**
     * Changes Type (if necessary) according to the result of _gettype_ and the old Type.
     *
     * @param string|null $typeValue result of _gettype_ called on variable
     * @param Type|null $oldType
     *
     * @return Type
     */
    public function changeType(?string $typeValue = null, ?Type $oldType = null): Type;
}