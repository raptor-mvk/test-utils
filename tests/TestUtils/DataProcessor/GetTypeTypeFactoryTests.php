<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataProcessor;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataProcessor\Type\ArrayType;
use Raptor\TestUtils\DataProcessor\Type\BoolType;
use Raptor\TestUtils\DataProcessor\Type\FloatType;
use Raptor\TestUtils\DataProcessor\Type\IntType;
use Raptor\TestUtils\DataProcessor\Type\MixedType;
use Raptor\TestUtils\DataProcessor\Type\StringType;
use Raptor\TestUtils\DataProcessor\Type\Type;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;
use Raptor\TestUtils\DataProcessor\TypeFactory\TypeFactory;

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
     * @param string $expectedTypeClass
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
            'random' => [MixedType::class, 'random']
        ];
    }

    /**
     * Checks that method _changeType_ returns correct Type instance when oldType is null.
     *
     * @param string $expectedTypeClass
     * @param string|null $typeValue
     * @param Type|null $oldType
     *
     * @dataProvider changeTypeDataProvider
     */
    public function testChangeTypeReturnsCorrectResult(
        string $expectedTypeClass,
        ?string $typeValue = null,
        ?Type $oldType = null
    ): void {
        $typeFactory = new GetTypeTypeFactory();

        $actual = $typeFactory->changeType($typeValue, $oldType);

        /** @noinspection UnnecessaryAssertionInspection __approved__ checks actual type not interface */
        static::assertInstanceOf($expectedTypeClass, $actual);
    }

    /**
     * Provides test data to verify _changeType_ method.
     *
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
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
     * @return array [ [ expectedTypeClass, typeValue, oldType ], ... ]
     */
    private function prepareFromIntTypeTestData(): array
    {
        return [
            'IntType -> boolean' => [MixedType::class, 'boolean', new IntType()],
            'IntType -> integer' => [IntType::class, 'integer', new IntType()],
            'IntType -> double' => [FloatType::class, 'double', new IntType()],
            'IntType -> string' => [MixedType::class, 'string', new IntType()],
            'IntType -> array' => [MixedType::class, 'array', new IntType()],
            'IntType -> null' => [MixedType::class, null, new IntType()],
            'IntType -> NULL' => [MixedType::class, 'NULL', new IntType()],
            'IntType -> random' => [MixedType::class, 'random', new IntType()]
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
            'FloatType -> boolean' => [MixedType::class, 'boolean', new FloatType()],
            'FloatType -> integer' => [FloatType::class, 'integer', new FloatType()],
            'FloatType -> double' => [FloatType::class, 'double', new FloatType()],
            'FloatType -> string' => [MixedType::class, 'string', new FloatType()],
            'FloatType -> array' => [MixedType::class, 'array', new FloatType()],
            'FloatType -> null' => [MixedType::class, null, new FloatType()],
            'FloatType -> NULL' => [MixedType::class, 'NULL', new FloatType()],
            'FloatType -> random' => [MixedType::class, 'random', new FloatType()]
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
            'BoolType -> boolean' => [BoolType::class, 'boolean', new BoolType()],
            'BoolType -> integer' => [MixedType::class, 'integer', new BoolType()],
            'BoolType -> double' => [MixedType::class, 'double', new BoolType()],
            'BoolType -> string' => [MixedType::class, 'string', new BoolType()],
            'BoolType -> array' => [MixedType::class, 'array', new BoolType()],
            'BoolType -> null' => [MixedType::class, null, new BoolType()],
            'BoolType -> NULL' => [MixedType::class, 'NULL', new BoolType()],
            'BoolType -> random' => [MixedType::class, 'random', new BoolType()]
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
            'StringType -> boolean' => [MixedType::class, 'boolean', new StringType()],
            'StringType -> integer' => [MixedType::class, 'integer', new StringType()],
            'StringType -> double' => [MixedType::class, 'double', new StringType()],
            'StringType -> string' => [StringType::class, 'string', new StringType()],
            'StringType -> array' => [MixedType::class, 'array', new StringType()],
            'StringType -> null' => [MixedType::class, null, new StringType()],
            'StringType -> NULL' => [MixedType::class, 'NULL', new StringType()],
            'StringType -> random' => [MixedType::class, 'random', new StringType()]
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
            'ArrayType -> boolean' => [MixedType::class, 'boolean', new ArrayType()],
            'ArrayType -> integer' => [MixedType::class, 'integer', new ArrayType()],
            'ArrayType -> double' => [MixedType::class, 'double', new ArrayType()],
            'ArrayType -> string' => [MixedType::class, 'string', new ArrayType()],
            'ArrayType -> array' => [ArrayType::class, 'array', new ArrayType()],
            'ArrayType -> null' => [MixedType::class, null, new ArrayType()],
            'ArrayType -> NULL' => [MixedType::class, 'NULL', new ArrayType()],
            'ArrayType -> random' => [MixedType::class, 'random', new ArrayType()]
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
            'MixedType -> boolean' => [MixedType::class, 'boolean', new MixedType()],
            'MixedType -> integer' => [MixedType::class, 'integer', new MixedType()],
            'MixedType -> double' => [MixedType::class, 'double', new MixedType()],
            'MixedType -> string' => [MixedType::class, 'string', new MixedType()],
            'MixedType -> array' => [MixedType::class, 'array', new MixedType()],
            'MixedType -> null' => [MixedType::class, null, new MixedType()],
            'MixedType -> NULL' => [MixedType::class, 'NULL', new MixedType()],
            'MixedType -> random' => [MixedType::class, 'random', new MixedType()]
        ];
    }
}
