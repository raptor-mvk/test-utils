<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\DataProcessor;

use Raptor\TestUtils\DataProcessor\TypeFactory\TypeFactoryInterface;

/**
 * Processes JSON string with test data. Used to generate service file for IDE.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class GeneratorDataProcessor extends AbstractJSONTestDataProcessor
{
    /**
     * @var TypeFactoryInterface $typeFactory
     */
    private $typeFactory;

    /**
     * @param TypeFactoryInterface $typeFactory
     */
    public function __construct(TypeFactoryInterface $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array      $element element without service field _\_name_
     * @param string     $name    value of service field _\_name_
     * @param array|null $default array of default values passed from higher levels
     */
    protected function processTestCase(array $element, string $name, ?array $default = null): void
    {
        $element = array_merge($default ?? [], $element);
        foreach ($element as $field => $value) {
            /** @var string $valueType */
            $valueType = gettype($value);
            $currentType = $this->getProcessed($field);
            $newType = (null === $currentType) ? $this->typeFactory->createType($valueType) :
                $this->typeFactory->changeType($currentType, $valueType);
            $this->addProcessed($field, $newType);
        }
    }
}
