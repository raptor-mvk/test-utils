<?php
declare(strict_types=1);

namespace Raptor\Test\Exceptions;

use RuntimeException;

/**
 * Исключение, выбрасываемое, если виртуальная файловая система не была инициализирована.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class VFSNotInitializedException extends RuntimeException
{
}