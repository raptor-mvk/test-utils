<?php
declare(strict_types=1);

namespace Raptor\Test\TestContainer;

/**
 * Контейнер данных для теста.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainer
{
    /** @var array  $data   данные для теста */
    private $data;

    /**
     * Конструктор контейнера.
     *
     * @param array     $data   данные для теста
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @noinspection MagicMethodsValidityInspection __approved__ метод __set не нужен */
    /**
     * @param string    $name   наименование получаемого свойства
     *
     * @return mixed|null       возвращаемое значение
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Возвращает все данные в массиве.
     *
     * @return array    все данные в массиве
     */
    public function getAllData(): array
    {
        return $this->data;
    }
}