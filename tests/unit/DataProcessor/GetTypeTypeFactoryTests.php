<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests\DataProcessor;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\DataProcessor\Type\TypeInterface;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class GetTypeTypeFactoryTests extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Checks that method _createType_ returns correct Type instance.
     *
     * @param string      $expectedTypeClass
     * @param string|null $typeValue
     *
     * @dataProvider createTypeDataProvider
     */
    public function testCreateTypeReturnsCorrectResult(string $expectedTypeClass, ?string $typeValue = null): void
    {
        $typeFactory = new GetTypeTypeFactory();

        $actual = $typeFactory->createType($typeValue);

        /** @noinspection UnnecessaryAssertionInspection __approved__ checks actual type not interface */
        static::assertInstanceOf($expectedTypeClass, $actual);
    }

    /**
     * Provides test data to verify _createType_ method.
     *
     * @return array [ [ expectedTypeClass, typeValue ], ... ]
     */
    public function createTypeDataProvider(): array
    {
        return [
            'boolean' => [BoolType::class, 'boolean'],
            'integer' => [IntType::class, 'integer'],
            'double' => [FloatType::class, 'double'],
            'string' => [StringType::class, 'string'],
            'array' => [ArrayType::class, 'array'],
            'null' => [MixedType::class, null],
            'NULL' => [MixedType::class, 'NULL'],
            'random' => [MixedType::class, 'random'],
        ];
    }

    /**
     * Checks that method _changeType_ returns correct Type instance when oldType is null.
     *
     * @param string             $expectedTypeClass
     * @param TypeInterface|null $oldType
     * @param string|null        $typeValue
     *
     * @dataProvider changeTypeDataProvider
     */
    public function testChangeTypeReturnsCorrectResult(string $expectedTypeClass, TypeInterface $oldType, ?string $typeValue = null): void
    {
        $typeFactory = new GetTypeTypeFactory();

        $actual = $typeFactory->changeType($oldType, $typeValue);

        /** @noinspection UnnecessaryAssertionInspection __approved__ checks actual type not interface */
        static::assertInstanceOf($expectedTypeClass, $actual);
    }

    /**
     * Provides test data to verify _changeType_ method.
     *
     * @return array [ [ expectedTypeClass, oldType, typeValue ], ... ]
     */
    public function changeTypeDataProvider(): array
    {
        return array_merge(
            $this->prepareFromIntTypeTestData(),
            $this->prepareFromFloatTypeTestData(),
            $this->prepareFromBoolTypeTestData(),
            $this->prepareFromStringTypeTestData(),
            $this->prepareFromArrayTypeTestData(),
            $this->prepareFromMixedTypeTestData(),
        );
    }

    /**
     * Prepares test data that has IntType as oldType.
     *
     * @return array [ [ expectedTypeClass, oldType, typeValue ], ... ]
     */
    private function prepareFromIntTypeTestData(): array
    {
        return [
            'IntType -> boolean' => [MixedType::class, new IntType(), 'boolean'],
            'IntType -> integer' => [IntType::class, new IntType(), 'integer'],
            'IntType -> double' => [FloatType::class, new IntType(), 'double'],
            'IntType -> string' => [MixedType::class, new IntType(), 'string'],
            'IntType -> array' => [MixedType::class, new IntType(), 'array'],
            'IntType -> null' => [MixedType::class, new IntType(), null],
            'IntType -> NULL' => [MixedType::class, new IntType(), 'NULL'],
            'IntType -> random' => [MixedType::class, new IntType(), 'random'],
        ];
    }

    /**
     * Prepares test data that has FloatType as oldType.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromFloatTypeTestData(): array
    {
        return [
            'FloatType -> boolean' => [MixedType::class, new FloatType(), 'boolean'],
            'FloatType -> integer' => [FloatType::class, new FloatType(), 'integer'],
            'FloatType -> double' => [FloatType::class, new FloatType(), 'double'],
            'FloatType -> string' => [MixedType::class, new FloatType(), 'string'],
            'FloatType -> array' => [MixedType::class, new FloatType(), 'array'],
            'FloatType -> null' => [MixedType::class, new FloatType(), null],
            'FloatType -> NULL' => [MixedType::class, new FloatType(), 'NULL'],
            'FloatType -> random' => [MixedType::class, new FloatType(), 'random'],
        ];
    }

    /**
     * Prepares test data that has BoolType as oldType.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromBoolTypeTestData(): array
    {
        return [
            'BoolType -> boolean' => [BoolType::class, new BoolType(), 'boolean'],
            'BoolType -> integer' => [MixedType::class, new BoolType(), 'integer'],
            'BoolType -> double' => [MixedType::class, new BoolType(), 'double'],
            'BoolType -> string' => [MixedType::class, new BoolType(), 'string'],
            'BoolType -> array' => [MixedType::class, new BoolType(), 'array'],
            'BoolType -> null' => [MixedType::class, new BoolType(), null],
            'BoolType -> NULL' => [MixedType::class, new BoolType(), 'NULL'],
            'BoolType -> random' => [MixedType::class, new BoolType(), 'random'],
        ];
    }

    /**
     * Prepares test data that has StringType as oldType.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromStringTypeTestData(): array
    {
        return [
            'StringType -> boolean' => [MixedType::class, new StringType(), 'boolean'],
            'StringType -> integer' => [MixedType::class, new StringType(), 'integer'],
            'StringType -> double' => [MixedType::class, new StringType(), 'double'],
            'StringType -> string' => [StringType::class, new StringType(), 'string'],
            'StringType -> array' => [MixedType::class, new StringType(), 'array'],
            'StringType -> null' => [MixedType::class, new StringType(), null],
            'StringType -> NULL' => [MixedType::class, new StringType(), 'NULL'],
            'StringType -> random' => [MixedType::class, new StringType(), 'random'],
        ];
    }

    /**
     * Prepares test data that has ArrayType as oldType.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromArrayTypeTestData(): array
    {
        return [
            'ArrayType -> boolean' => [MixedType::class, new ArrayType(), 'boolean'],
            'ArrayType -> integer' => [MixedType::class, new ArrayType(), 'integer'],
            'ArrayType -> double' => [MixedType::class, new ArrayType(), 'double'],
            'ArrayType -> string' => [MixedType::class, new ArrayType(), 'string'],
            'ArrayType -> array' => [ArrayType::class, new ArrayType(), 'array'],
            'ArrayType -> null' => [MixedType::class, new ArrayType(), null],
            'ArrayType -> NULL' => [MixedType::class, new ArrayType(), 'NULL'],
            'ArrayType -> random' => [MixedType::class, new ArrayType(), 'random'],
        ];
    }

    /**
     * Prepares test data that has MixedType as oldType.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromMixedTypeTestData(): array
    {
        return [
            'MixedType -> boolean' => [MixedType::class, new MixedType(), 'boolean'],
            'MixedType -> integer' => [MixedType::class, new MixedType(), 'integer'],
            'MixedType -> double' => [MixedType::class, new MixedType(), 'double'],
            'MixedType -> string' => [MixedType::class, new MixedType(), 'string'],
            'MixedType -> array' => [MixedType::class, new MixedType(), 'array'],
            'MixedType -> null' => [MixedType::class, new MixedType(), null],
            'MixedType -> NULL' => [MixedType::class, new MixedType(), 'NULL'],
            'MixedType -> random' => [MixedType::class, new MixedType(), 'random'],
        ];
    }
}
