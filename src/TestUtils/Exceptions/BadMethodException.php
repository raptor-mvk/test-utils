<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Exceptions;

use RuntimeException;

/**
 * Exception, that is thrown when _ExtraUtils::invokeMethod_ is called with incorrect method name.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BadMethodException extends RuntimeException
{
}
