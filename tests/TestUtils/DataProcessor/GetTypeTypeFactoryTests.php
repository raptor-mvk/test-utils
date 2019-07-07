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
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class GetTypeTypeFactoryTests extends TestCase
{
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
}