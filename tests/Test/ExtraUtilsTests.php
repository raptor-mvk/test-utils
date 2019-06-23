<?php
declare(strict_types=1);

namespace RaptorTests\Test;

use PHPUnit\Framework\TestCase;
use Raptor\Test\Exceptions\BadMethodException;
use Raptor\Test\ExtraUtils;
use RaptorTests\Test\Utils\InvokeMethodTestObject;

/**
 * Класс с тестами для трейта _ExtraUtils_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class ExtraUtilsTests extends TestCase
{
    use ExtraUtils;

    /**
     * Проверяет, что метод _invokeMethod_ выбрасывает исключение _BadMethodException_, если указан неверный метод.
     */
    public function testInvokeMethodThrowsReflectionExceptionWhenMethodIsIncorrect(): void
    {
        $method = 'replace';
        $this->expectException(BadMethodException::class);
        $this->expectExceptionMessageRegExp("/^Указан отсутствующий метод $method$/");

        $object = new InvokeMethodTestObject();

        static::invokeMethod($object, $method);
    }

    /**
     * Проверяет, что метод _invokeMethod_ вызывает требуемый метод с заданными параметрами.
     *
     * @param string    $method        наименование метода
     * @param array     $parameters    параметры метода
     * @param string    $expected      ожидаемый результат
     *
     * @dataProvider invokeMethodDataProvider
     */
    public function testInvokeMethodCallsCorrectMethod(string $method, array $parameters, string $expected): void
    {
        $object = new InvokeMethodTestObject();

        $actual = static::invokeMethod($object, $method, $parameters);

        static::assertSame($expected, $actual);
    }

    /**
     * Предоставляет тестовые данные для метода _invokeMethod_.
     *
     * @return array    массив тестовых данных в формате [ [ method, parameters, expected ], ... ]
     */
    public function invokeMethodDataProvider(): array
    {
        $palindrome = 'delia saw I was ailed';
        $extra = 'a';
        $count = 2;
        return [
            'protected' => ['reverse', [$palindrome.$extra], $extra.$palindrome],
            'private' => ['repeat', [$palindrome, $count], str_repeat($palindrome, $count)]
        ];
    }
}
