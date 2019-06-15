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
    use ExtraUtils;

    /**
     * Утверждение, проверяющее, что два массива полностью совпадают.
     *
     * @param array          $expected    ожидаемый массив
     * @param array          $actual      полученный массив
     * @param string|null    $message     сообщение об ошибке, выдаваемое в случае различий между массивами
     */
    public static function assertArraysAreSame(array $expected, array $actual, ?string $message = null): void
    {
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }

    /**
     * Утверждение, проверяющее, что два массива содержат одинаковые элементы без учёта их порядка на верхнем уровне для
     * ассоциативных массивов.
     *
     * @param array          $expected    ожидаемый массив
     * @param array          $actual      полученный массив
     * @param string|null    $message     сообщение об ошибке, выдаваемое в случае различий между массивами
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

    /**
     * Рекурсивно сортирует массив по ключу.
     *
     * @param array    $array    сортируемый массив
     */
    private static function ksortRecursive(array &$array): void
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                static::ksortRecursive($array[$key]);
            }
        }
    }

    /**
     * Утверждение, проверяющее, что два массива содержат одинаковые элементы без учёта их порядка на всех уровнях для
     * ассоциативных массивов.
     *
     * @param array          $expected    ожидаемый массив
     * @param array          $actual      полученный массив
     * @param string|null    $message     сообщение об ошибке, выдаваемое в случае различий между массивами
     */
    public static function assertArraysAreSameIgnoringOrderRecursively(
        array $expected,
        array $actual,
        ?string $message = null
    ): void {
        static::ksortRecursive($expected);
        static::ksortRecursive($actual);
        $expectedString = json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actualString = json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        static::assertSame($expectedString, $actualString, $message ?? '');
    }
}
