<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor;

use Raptor\TestUtils\Exceptions\DataParseException;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;

/**
 * Processes JSON string with test data. Used to wrap test data into TestDataContainers.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestContainerWrapperDataProcessor extends AbstractJSONTestDataProcessor
{
    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array $element
     * @param array|null $default array of default values passed from higher levels
     */
    protected function processTestCase(array $element, ?array $default = null): void
    {
        $name = $element[self::NAME_KEY];
        unset($element[self::NAME_KEY]);
        if ($this->hasProcessed($name)) {
            throw new DataParseException("Non-unique test name $name was found.");
        }
        $this->addProcessed($name, new TestDataContainer(array_merge($default ?? [], $element)));
    }
}
