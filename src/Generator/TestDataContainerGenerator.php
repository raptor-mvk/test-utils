<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\Generator;

use Raptor\TestUtils\DataLoader\DirectoryDataLoaderInterface;
use Raptor\TestUtils\DataProcessor\Type\TypeInterface;
use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;

/**
 * Generates service file for IDE. Service file used to autocomplete.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class TestDataContainerGenerator implements GeneratorInterface
{
    /** @var DirectoryDataLoaderInterface $directoryDataLoader */
    private $directoryDataLoader;

    /**
     * @param DirectoryDataLoaderInterface $directoryDataLoader
     */
    public function __construct(DirectoryDataLoaderInterface $directoryDataLoader)
    {
        $this->directoryDataLoader = $directoryDataLoader;
    }

    /**
     * Returns contents of service file for IDE that is generated using data from all JSON files found recursively in
     * the given directory.
     *
     * @param string $path path to directory with data files
     *
     * @return string
     *
     * @throws DataDirectoryNotFoundException
     */
    public function generate(string $path): string
    {
        $loadedData = $this->directoryDataLoader->load($path, '/^.*\.json$/');
        $result = "<?php\n\nuse Raptor\TestUtils\TestDataContainer\TestDataContainer;\n";
        foreach ($loadedData as $className => $fields) {
            /** @var array $fields */
            $result .= (('' !== $result) ? "\n" : '')."/**\n";
            foreach ($fields as $field => $type) {
                $methodName = $this->getMethodName($field, $type);
                $result .= " * @method $type $methodName()\n";
            }
            $result .= " */\nclass {$className}DataContainer extends TestDataContainer\n{\n}\n";
        }

        return $result;
    }

    /**
     * Returns array of errors that occurred during the last generation.
     *
     * @return array [ filename => errorMessage, ... ]
     */
    public function getLastErrors(): array
    {
        return $this->directoryDataLoader->getLastErrors();
    }

    /**
     * Returns name of getter method. Type is necessary for bool getters names.
     *
     * @param string        $field field name
     * @param TypeInterface $type  field type
     *
     * @return string
     */
    private function getMethodName(string $field, TypeInterface $type): string
    {
        $isBool = $type->isBool();
        if ($isBool && (0 === strncmp($field, 'is_', 3))) {
            $field = substr($field, 3);
        }
        $key = ucfirst(str_replace('_', '', ucwords($field, '_')));

        return $isBool ? "is$key" : "get$key";
    }
}
