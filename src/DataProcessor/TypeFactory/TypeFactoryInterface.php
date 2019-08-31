<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\DataProcessor\TypeFactory;

use Raptor\TestUtils\DataProcessor\Type\TypeInterface;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface TypeFactoryInterface
{
    /**
     * Creates Type corresponding to _gettype_ result.
     *
     * @param string|null $typeValue result of _gettype_ called on variable
     *
     * @return TypeInterface
     */
    public function createType(?string $typeValue = null): TypeInterface;

    /**
     * Changes Type (if necessary) according to the result of _gettype_ and the old Type.
     *
     * @param TypeInterface $oldType
     * @param string|null   $typeValue result of _gettype_ called on variable
     *
     * @return TypeInterface
     */
    public function changeType(TypeInterface $oldType, ?string $typeValue = null): TypeInterface;
}
