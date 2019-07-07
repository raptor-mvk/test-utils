<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor;

use Raptor\TestUtils\DataProcessor\TypeFactory\TypeFactory;

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
     * @var TypeFactory $typeFactory
     */
    private $typeFactory;

    /**
     * @param TypeFactory $typeFactory
     */
    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * Processes test case (element with service field _name), considering it to be correct (without error handling).
     *
     * @param array $element element without service field _\_name_
     * @param string $name value of service field _\_name_
     * @param array|null $default array of default values passed from higher levels
     */
    protected function processTestCase(array $element, string $name, ?array $default = null): void
    {
        foreach ($element as $field => $value) {
            /** @var string $valueType */
            $valueType = gettype($value);
            $currentType = $this->getProcessed($field);
            $newType = $this->typeFactory->changeType($valueType, $currentType);
            $this->addProcessed($field, $newType);
        }
    }
}
