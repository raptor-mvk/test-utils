<?php
declare(strict_types=1);

namespace Raptor\TestUtils;

use Raptor\TestUtils\Exceptions\BadMethodException;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait with service testing methods.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraUtils
{
    /**
     * Invokes protected or private method of the given object.
     *
     * @param object $object
     * @param string $methodName name of the method to call
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
     * Encodes JSON: unescapes Unicode, prettifies output and throws exceptions on errors.
     *
     * @param mixed $input input
     *
     * @return string encoded JSON string
     */
    public static function jsonEncodePrettily($input): string
    {
        return json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
