<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

use JsonException;
use Raptor\Test\Exceptions\DataParseException;

/**
 * Абстрактный класс обработчика JSON-файлов.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class AbstractJSONTestDataProcessor implements DataProcessor
{
    /** @var string CHILDREN_KEY     ключ для служебного поля, содержащего дочерние элементы */
    protected const CHILDREN_KEY = '_children';

    /** @var string NAME_KEY        ключ для служебного поля, содержащего наименование теста */
    protected const NAME_KEY = '_name';

    /**
     * Декодирует JSON-строку и возвращает данные в виде ассоциативного массива.
     *
     * @param string    $json       JSON-строка
     *
     * @return array                декодированные данные
     *
     * @throws DataParseException   ошибка обработки данных
     */
    protected function decodeData(string $json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } /** @noinspection PhpRedundantCatchClauseInspection __approved__ */ catch (JsonException $e) {
            /** JsonException может быть выброшено */
            throw new DataParseException('Ошибка при разборе JSON-данных', 0, $e);
        }
    }
}
