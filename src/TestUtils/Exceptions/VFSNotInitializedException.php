<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Exceptions;

use RuntimeException;

/**
 * Exception that is thrown when virtual file system was not initialized.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class VFSNotInitializedException extends RuntimeException
{
}
