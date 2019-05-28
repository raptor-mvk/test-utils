<?php
declare(strict_types=1);

namespace Raptor\Test\Exceptions;

use RuntimeException;

/**
 * Исключение, выбрасываемое при неверно указанном методе для метода _ExtraUtils::invokeMethod_
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BadMethodException extends RuntimeException
{
}
