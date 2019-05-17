<?php
declare(strict_types=1);

namespace Raptor\Test;

/**
 * Трейт с утверждениями и вспомогательными методами для тестирования.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraAssertions
{
    /**
     * Утверждение, проверяющее, что два массива полностью совпадают.
     *
     * @param array $expected       ожидаемый массив
     * @param array $actual         полученный массив
     * @param string|null $message  сообщение об ошибке, выдаваемое в случае различий между массивами
     */
    public static function assertArraysAreSame(array $expected, array $actual, ?string $message = null): void
    {
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }

    /**
     * Утверждение, проверяющее, что два массива содержат одинаковые элементы в произвольном порядке.
     *
     * @param array $expected       ожидаемый массив
     * @param array $actual         полученный массив
     * @param string|null $message  сообщение об ошибке, выдаваемое в случае различий между массивами
     */
    public static function assertArraysAreSameIgnoringOrder(
        array $expected,
        array $actual,
        ?string $message = null
    ): void {
        ksort($expected);
        ksort($actual);
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }
}