<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\DataProcessor\Type\Type;

/**
 * Tests for classes that implement Type.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class CommonTypeTests extends TestCase
{
    /**
     * Checks that method _isBool_ returns correct value for a given Type instance.
     *
     * @param Type $type
     * @param bool $expected
     *
     * @dataProvider isBoolDataProvider
     */
    public function testIsBoolReturnsCorrectValue(Type $type, bool $expected): void
    {
        $actual = $type->isBool();

        static::assertSame($expected, $actual);
    }

    /**
     * Provides test data to verify _isBool_ method.
     *
     * @return array [ [ type, expected ], ... ]
     */
    public function isBoolDataProvider(): array
    {
        return [
            'int' => [new IntType(), false],
            'float' => [new FloatType(), false],
            'bool' => [new BoolType(), true],
            'string' => [new StringType(), false],
            'array' => [new ArrayType(), false],
            'mixed' => [new MixedType(), false]
        ];
    }

    /**
     * Checks that method _\_\_toString_ returns correct value for a given Type instance.
     *
     * @param Type $type
     * @param string $expected
     *
     * @dataProvider toStringDataProvider
     */
    public function testToStringReturnsCorrectValue(Type $type, string $expected): void
    {
        static::assertSame($expected, (string)$type);
    }

    /**
     * Provides test data to verify _\_\_toString_ method.
     *
     * @return array [ [ type, expected ], ... ]
     */
    public function toStringDataProvider(): array
    {
        return [
            'int' => [new IntType(), 'int'],
            'float' => [new FloatType(), 'float'],
            'bool' => [new BoolType(), 'bool'],
            'string' => [new StringType(), 'string'],
            'array' => [new ArrayType(), 'array'],
            'mixed' => [new MixedType(), 'mixed']
        ];
    }

    /**
     * Checks that method _getCommonType_ returns correct value for given Type instances.
     *
     * @param Type $oldType current Type
     * @param Type $newType newly acquired Type
     * @param string $expectedTypeClass
     *
     * @dataProvider getCommonTypeDataProvider
     */
    public function testGetCommonTypeReturnsCorrectValue(
        Type $oldType,
        Type $newType,
        string $expectedTypeClass
    ): void {
        $actual = $oldType->getCommonType($newType);

        /** @noinspection UnnecessaryAssertionInspection __approved__ checks actual type, not interface */
        static::assertInstanceOf($expectedTypeClass, $actual);
    }

    /**
     * Provides test data to verify _getCommonType_ method.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    public function getCommonTypeDataProvider(): array
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
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromIntTypeTestData(): array
    {
        return [
            'int -> int' => [new IntType(), new IntType(), IntType::class],
            'int -> float' => [new IntType(), new FloatType(), FloatType::class],
            'int -> bool' => [new IntType(), new BoolType(), MixedType::class],
            'int -> array' => [new IntType(), new ArrayType(), MixedType::class],
            'int -> string' => [new IntType(), new StringType(), MixedType::class],
            'int -> mixed' => [new IntType(), new MixedType(), MixedType::class]
        ];
    }

    /**
     * Prepares test data that has FloatType as oldType.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromFloatTypeTestData(): array
    {
        return [
            'float -> int' => [new FloatType(), new IntType(), FloatType::class],
            'float -> float' => [new FloatType(), new FloatType(), FloatType::class],
            'float -> bool' => [new FloatType(), new BoolType(), MixedType::class],
            'float -> array' => [new FloatType(), new ArrayType(), MixedType::class],
            'float -> string' => [new FloatType(), new StringType(), MixedType::class],
            'float -> mixed' => [new FloatType(), new MixedType(), MixedType::class]
        ];
    }

    /**
     * Prepares test data that has BoolType as oldType.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromBoolTypeTestData(): array
    {
        return [
            'bool -> int' => [new BoolType(), new IntType(), MixedType::class],
            'bool -> float' => [new BoolType(), new FloatType(), MixedType::class],
            'bool -> bool' => [new BoolType(), new BoolType(), BoolType::class],
            'bool -> array' => [new BoolType(), new ArrayType(), MixedType::class],
            'bool -> string' => [new BoolType(), new StringType(), MixedType::class],
            'bool -> mixed' => [new BoolType(), new MixedType(), MixedType::class]
        ];
    }

    /**
     * Prepares test data that has StringType as oldType.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromStringTypeTestData(): array
    {
        return [
            'string -> int' => [new StringType(), new IntType(), MixedType::class],
            'string -> float' => [new StringType(), new FloatType(), MixedType::class],
            'string -> bool' => [new StringType(), new BoolType(), MixedType::class],
            'string -> array' => [new StringType(), new ArrayType(), MixedType::class],
            'string -> string' => [new StringType(), new StringType(), StringType::class],
            'string -> mixed' => [new StringType(), new MixedType(), MixedType::class]
        ];
    }

    /**
     * Prepares test data that has ArrayType as oldType.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromArrayTypeTestData(): array
    {
        return [
            'array -> int' => [new ArrayType(), new IntType(), MixedType::class],
            'array -> float' => [new ArrayType(), new FloatType(), MixedType::class],
            'array -> bool' => [new ArrayType(), new BoolType(), MixedType::class],
            'array -> array' => [new ArrayType(), new ArrayType(), ArrayType::class],
            'array -> string' => [new ArrayType(), new StringType(), MixedType::class],
            'array -> mixed' => [new ArrayType(), new MixedType(), MixedType::class]
        ];
    }

    /**
     * Prepares test data that has MixedType as oldType.
     *
     * @return array [ [ oldType, newType, expectedTypeClass ], ... ]
     */
    private function prepareFromMixedTypeTestData(): array
    {
        return [
            'mixed -> int' => [new MixedType(), new IntType(), MixedType::class],
            'mixed -> float' => [new MixedType(), new FloatType(), MixedType::class],
            'mixed -> bool' => [new MixedType(), new BoolType(), MixedType::class],
            'mixed -> array' => [new MixedType(), new ArrayType(), MixedType::class],
            'mixed -> string' => [new MixedType(), new StringType(), MixedType::class],
            'mixed -> mixed' => [new MixedType(), new MixedType(), MixedType::class]
        ];
    }
}
