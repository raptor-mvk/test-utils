<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\DataProcessor\TypeFactory;

use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\DataProcessor\Type\TypeInterface;

/**
 * Factory for types based on result of _gettype_.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class GetTypeTypeFactory implements TypeFactoryInterface
{
    /** @var array TYPE_MAPPING map for _createType_ method */
    private const TYPE_MAPPING = [
        'boolean' => BoolType::class,
        'integer' => IntType::class,
        'double' => FloatType::class,
        'string' => StringType::class,
        'array' => ArrayType::class,
    ];

    /** @var string COMMON_TYPE Type that is used in any unknown situation */
    private const COMMON_TYPE = MixedType::class;

    /**
     * Creates Type corresponding to _gettype_ result.
     *
     * @param string|null $typeValue result of _gettype_ called on variable
     *
     * @return TypeInterface
     */
    public function createType(?string $typeValue = null): TypeInterface
    {
        $type = self::TYPE_MAPPING[$typeValue ?? ''] ?? self::COMMON_TYPE;

        return new $type();
    }

    /**
     * Changes Type (if necessary) according to the result of _gettype_ and the old Type.
     *
     * @param TypeInterface $oldType
     * @param string|null   $typeValue result of _gettype_ called on variable
     *
     * @return TypeInterface
     */
    public function changeType(TypeInterface $oldType, ?string $typeValue = null): TypeInterface
    {
        $newType = $this->createType($typeValue);

        return $oldType->getCommonType($newType);
    }
}
