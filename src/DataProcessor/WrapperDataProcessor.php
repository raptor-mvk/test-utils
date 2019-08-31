<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

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
final class WrapperDataProcessor extends AbstractJSONTestDataProcessor
{
    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array      $element element without service field _\_name_
     * @param string     $name    value of service field _\_name_
     * @param array|null $default array of default values passed from higher levels
     */
    protected function processTestCase(array $element, string $name, ?array $default = null): void
    {
        if ($this->hasProcessed($name)) {
            throw new DataParseException("Non-unique test name $name was found.");
        }
        $this->addProcessed($name, [new TestDataContainer(array_merge($default ?? [], $element))]);
    }
}
