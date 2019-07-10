<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor;

use function is_string;
use JsonException;
use Raptor\TestUtils\Exceptions\DataParseException;

/**
 * Abstract base class for processor handling JSON files with test data.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class AbstractJSONTestDataProcessor implements DataProcessor
{
    /** @var string CHILDREN_KEY key for service field that contains child elements */
    private const CHILDREN_KEY = '_children';

    /** @var string NAME_KEY key for service field that contains test's name */
    private const NAME_KEY = '_name';

    /** @var array $processedData current processed data */
    private $processedData;

    /**
     * Processes the provided JSON string and returns processing result.
     *
     * @param string $data JSON string with test data
     *
     * @return array
     *
     * @throws DataParseException
     */
    public function process(string $data): array
    {
        $decodedData = $this->decodeData($data);
        $this->processedData = [];
        $this->processArrayElement($decodedData, 'root');
        return $this->processedData;
    }

    /**
     * Decodes JSON string and returns associative array. Used to wrap JsonException.
     *
     * @param string $json
     *
     * @return array
     *
     * @throws DataParseException
     */
    private function decodeData(string $json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } /** @noinspection PhpRedundantCatchClauseInspection __approved__ */ catch (JsonException $e) {
            /** JsonException could be thrown */
            throw new DataParseException('JSON parse error.', 0, $e);
        }
    }

    /**
     * Processes element that should be JSON array.
     *
     * @param array $element
     * @param string $level path to element, used in error message
     * @param array|null $default array of default values passed from higher levels
     */
    private function processArrayElement(array $element, string $level, ?array $default = null): void
    {
        if ($element !== array_values($element)) {
            throw new DataParseException("Expected array, object found at level $level.");
        }
        $i = 0;
        foreach ($element as $item) {
            $this->processCommonObjectElement($item, "$level.$i", $default);
            $i++;
        }
    }

    /**
     * Processes element that should be JSON object.
     *
     * @param array $element
     * @param string $level path to element, used in error message
     * @param array|null $default array of default values passed from higher levels
     */
    private function processCommonObjectElement(array $element, string $level, ?array $default = null): void
    {
        $fields = array_keys($element);
        $this->checkFields($fields, $level);
        $hasChildren = isset($element[self::CHILDREN_KEY]);
        if ($hasChildren) {
            $this->processObjectElementWithChildren($element, $level, $default);
            return;
        }
        $this->processObjectElementWithoutChildren($element, $level, $default);
    }

    /**
     * Checks that array of field names does not contain incorrect field names or unknown service field names (starting
     * from "_"). If error was found, then throws DataParseException.
     *
     * @param array $fields array of field names
     * @param string $level path to element, used in error message
     *
     * @throws DataParseException
     */
    private function checkFields(array $fields, string $level): void
    {
        foreach ($fields as $field) {
            if (($field !== self::NAME_KEY) && ($field !== self::CHILDREN_KEY) && ($field[0] === '_')) {
                throw new DataParseException("Unknown service field $field at level $level.");
            }
            if (preg_match('/[^a-z_]/', $field) !== 0) {
                throw new DataParseException("Field name $field at level $level contains forbidden characters.");
            }
        }
    }

    /**
     * Processes element that is JSON object and contains child elements.
     *
     * @param array $element
     * @param string $level path to element, used in error message
     * @param array|null $default array of default values passed from higher levels
     */
    private function processObjectElementWithChildren(array $element, string $level, ?array $default = null): void
    {
        if (isset($element[self::NAME_KEY])) {
            throw new DataParseException("Element at level $level contains both name and child elements.");
        }
        $children = $element[self::CHILDREN_KEY];
        unset($element[self::CHILDREN_KEY]);
        $this->processArrayElement($children, $level, array_merge($default ?? [], $element));
    }

    /**
     * Processes element that is JSON object and does not contain child elements.
     *
     * @param array $element
     * @param string $level path to element, used in error message
     * @param array|null $default array of default values passed from higher levels
     */
    private function processObjectElementWithoutChildren(array $element, string $level, ?array $default = null): void
    {
        $hasName = isset($element[self::NAME_KEY]);
        if (!$hasName) {
            throw new DataParseException("Test name not found at level $level.");
        }
        $name = $element[self::NAME_KEY];
        if (!is_string($name)) {
            throw new DataParseException("Test name is not string at level $level.");
        }
        if ($name === '') {
            throw new DataParseException("Empty test name at level $level.");
        }
        unset($element[self::NAME_KEY]);
        $this->processTestCase($element, $name, $default);
    }

    /**
     * Adds new element with given key to processed data.
     *
     * @param string $key
     * @param mixed $data
     */
    protected function addProcessed(string $key, $data): void
    {
        $this->processedData[$key] = $data;
    }

    /**
     * Checks that processed data contains element with given key.
     *
     * @param string $key
     *
     * @return bool _true_ if processed data contains element with given key, _false_ otherwise
     */
    protected function hasProcessed(string $key): bool
    {
        return isset($this->processedData[$key]);
    }

    /**
     * Returns element with given key from processed data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    protected function getProcessed(string $key)
    {
        return $this->processedData[$key] ?? null;
    }

    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array $element element without service field _\_name_
     * @param string $name value of service field _\_name_
     * @param array|null $default array of default values passed from higher levels
     */
    abstract protected function processTestCase(array $element, string $name, ?array $default = null): void;
}
