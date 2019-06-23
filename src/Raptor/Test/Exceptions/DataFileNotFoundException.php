<?php
declare(strict_types=1);

namespace Raptor\Test\Exceptions;

use RuntimeException;

/**
 * Исключение, выбрасываемое при отсутствии файла с данными.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataFileNotFoundException extends RuntimeException
{
}
