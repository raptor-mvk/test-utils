<?php
declare(strict_types=1);

namespace Raptor\Test\TestDataContainer;

/**
 * Контейнер данных для теста.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainer
{
    /** @var array    $data    данные для теста */
    private $data;

    /**
     * Конструктор контейнера.
     *
     * @param array    $data    данные для теста
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string        $method        наименование вызываемого метода
     * @param array|null    $parameters    параметры вызываемого метода
     *
     * @return mixed|null    возвращаемое значение
     */
    public function __call(string $method, ?array $parameters = null)
    {
        $result = null;
        $fieldCamelCase = (strncmp($method, 'get', 3) === 0) ? substr($method, 3) : $method;
        $field = strtolower(preg_replace('/(?!^)([A-Z])/', '_$1', $fieldCamelCase));
        if (isset($this->data[$field])) {
            $result = $this->data[$field];
        }
        return $result;
    }

    /**
     * Возвращает все данные в массиве.
     *
     * @return array    все данные в массиве
     */
    public function allData(): array
    {
        return $this->data;
    }
}
