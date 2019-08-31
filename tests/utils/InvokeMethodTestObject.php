<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\TestUtils;

/**
 * Service class for testing _ExtraUtilsTrait::invokeMethod_
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class InvokeMethodTestObject
{
    /**
     * Reverses input string.
     *
     * @param string $input
     *
     * @return string
     *
     * @noinspection PhpUnused __approved__ used via Reflection
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
     * @param int    $count
     *
     * @return string
     *
     * @noinspection PhpUnused __approved__ used via Reflection
     */
    private function repeat(string $input, int $count): string
    {
        return str_repeat($input, $count);
    }
}
