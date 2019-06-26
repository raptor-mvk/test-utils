<?php
declare(strict_types=1);

namespace Raptor\TestUtils\TestDataContainer;

/**
 * Container for test data.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainer
{
    /** @var array $data array with test data */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $method name of the called method
     * @param array|null $parameters parameters of the called method
     *
     * @return mixed|null
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
     * Returns test data array. Used for testing purposes.
     *
     * @return array
     */
    public function allData(): array
    {
        return $this->data;
    }
}
