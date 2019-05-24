<?php
declare(strict_types=1);

namespace Raptor\Test;

use Raptor\Test\Exceptions\BadMethodException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Трейт с вспомогательными методами для тестирования.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait ExtraUtils
{
    /**
     * @brief Вызывает защищённый или приватный метод объекта.
     *
     * @param object        $object         объект
     * @param string        $methodName     имя метода
     * @param array|null    $parameters     параметры метода
     *
     * @return mixed                        возвращаемое значение метода
     *
     * @throws BadMethodException           неверно указанный метод
     */
    public static function invokeMethod(object $object, string $methodName, ?array $parameters = null)
    {
        $class = \get_class($object);
        try {
            $method = new ReflectionMethod($class, $methodName);
            $method->setAccessible(true);
            return $method->invokeArgs($object, $parameters ?? []);
        } catch (ReflectionException $e) {
            throw new BadMethodException("Указан отсутствующий метод $methodName", 0, $e);
        }
    }

}