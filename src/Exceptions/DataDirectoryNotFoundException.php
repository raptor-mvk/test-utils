<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\Exceptions;

use RuntimeException;

/**
 * Exception that is thrown when directory with data files was not found.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class DataDirectoryNotFoundException extends RuntimeException
{
}
