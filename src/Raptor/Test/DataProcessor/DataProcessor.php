<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

use Raptor\Test\Exceptions\DataParseException;

/**
 * Интерфейс обработчика тестовых данных.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DataProcessor
{
    /**
     * Выполняет обработку тестовых данных.
     *
     * @param string    $data    строка с тестовыми данными
     *
     * @return array    преобразованные и обработанные данные
     *
     * @throws DataParseException    ошибка обработки данных
     */
    public function process(string $data): array;
}
