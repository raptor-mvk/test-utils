<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils;

use Raptor\TestUtils\Exceptions\BadMethodException;
use ReflectionException;
use ReflectionMethod;
use function get_class;

/**
 * Trait with service testing methods.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraUtilsTrait
{
    /**
     * Invokes protected or private method of the given object.
     *
     * @param object     $object
     * @param string     $methodName name of the method to call
     * @param array|null $parameters parameters of the method to call
     *
     * @return mixed returned value of the method
     *
     * @throws BadMethodException
     */
    public static function invokeMethod(object $object, string $methodName, ?array $parameters = null)
    {
        $class = get_class($object);
        try {
            $method = new ReflectionMethod($class, $methodName);
            $method->setAccessible(true);

            return $method->invokeArgs($object, $parameters ?? []);
        } catch (ReflectionException $e) {
            throw new BadMethodException("Method $methodName was not found.", 0, $e);
        }
    }

    /**
     * Checks that an exception is thrown with exact given message.
     *
     * @param string $message expected exact message
     */
    public function expectExceptionExactMessage(string $message): void
    {
        $message = preg_quote($message, '/');
        $this->expectExceptionMessageRegExp("/^$message$/");
    }
}
