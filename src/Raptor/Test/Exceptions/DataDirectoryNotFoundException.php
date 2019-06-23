<?php
declare(strict_types=1);

namespace Raptor\Test\Exceptions;

use RuntimeException;

/**
 * Исключение, выбрасываемое при отсутствии директории с данными.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataDirectoryNotFoundException extends RuntimeException
{
}
