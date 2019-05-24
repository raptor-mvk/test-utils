<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader\Utils;

/**
 * Тестовый контейнер с данными.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainer
{
    /** @var array $data    массив с данными */
    private $data;

    /**
     * Конструктор контейнера с данными.
     *
     * @param array $data   массив с данными
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Возвращает данные.
     *
     * @return array    данные, переданные в конструктор
     */
    public function getData(): array
    {
        return $this->data;
    }
}
