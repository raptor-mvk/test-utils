<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Exceptions;

use RuntimeException;

/**
 * Exception that is thrown, when directory with data files was not found.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataDirectoryNotFoundException extends RuntimeException
{
}
