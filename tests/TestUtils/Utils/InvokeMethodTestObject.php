<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\Utils;

/**
 * Класс для тестирования метода _ExtraUtils::invokeMethod_
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class InvokeMethodTestObject
{
    /**
     * Переворачивает строку.
     *
     * @param string    $input    исходная строка
     *
     * @return string    перевёрнутая строка
     */
    protected function reverse(string $input): string
    {
        return strrev($input);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection __approved__  used for testing purposes only */
    /**
     * Повторяет строку заданное число раз.
     *
     * @param string    $input    исходная строка
     * @param int       $count    количество повторений
     *
     * @return string    результирующая строка
     */
    private function repeat(string $input, int $count): string
    {
        return str_repeat($input, $count);
    }
}
