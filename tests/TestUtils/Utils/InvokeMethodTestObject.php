<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\Utils;

/**
 * Service class for testing _ExtraUtils::invokeMethod_
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class InvokeMethodTestObject
{
    /**
     * Reverses input string.
     *
     * @param string $input
     *
     * @return string
     */
    protected function reverse(string $input): string
    {
        return strrev($input);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection __approved__  used for testing purposes only */
    /**
     * Repeats the given string given number of times.
     *
     * @param string $input
     * @param int $count
     *
     * @return string
     */
    private function repeat(string $input, int $count): string
    {
        return str_repeat($input, $count);
    }
}
