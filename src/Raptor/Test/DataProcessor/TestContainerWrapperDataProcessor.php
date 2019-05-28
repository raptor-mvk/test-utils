<?php
declare(strict_types=1);

namespace Raptor\Test\DataProcessor;

use Raptor\Test\Exceptions\DataParseException;
use Raptor\Test\TestDataContainer\TestDataContainer;

/**
 * Обработчик тестовых данных в формате JSON, используемый для загрузки данных в контейнеры.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class TestContainerWrapperDataProcessor extends AbstractJSONTestDataProcessor
{
    /**
     * Обрабатывает тестовый набор данных (элемент, содержащий служебное поле _name), предполагая, что он корректен
     * (без обработки ошибок).
     *
     * @param array         $element    обрабатываемый элемент
     * @param array|null    $default    значения по умолчанию с вышестоящих уровней
     */
    protected function processTestCase(array $element, ?array $default = null): void
    {
        $name = $element[self::NAME_KEY];
        unset($element[self::NAME_KEY]);
        if ($this->hasProcessed($name)) {
            throw new DataParseException("Обнаружено неуникальное наименование $name");
        }
        $this->addProcessed($name, new TestDataContainer(array_merge($default ?? [], $element)));
    }
}
