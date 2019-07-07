<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Generator;

use Raptor\TestUtils\DataLoader\DirectoryDataLoader;
use Raptor\TestUtils\DataProcessor\GeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\Type\Type;
use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;

/**
 * Generates service file for IDE. Service file used to autocomplete.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class TestDataContainerGenerator implements Generator
{
    /** @var DirectoryDataLoader $directoryDataLoader */
    private $directoryDataLoader;

    /**
     * @param DirectoryDataLoader $directoryDataLoader
     */
    public function __construct(DirectoryDataLoader $directoryDataLoader)
    {
        $this->directoryDataLoader = $directoryDataLoader;
    }

    /**
     * Returns name of getter method. Type is necessary for bool getters names.
     *
     * @param string $field field name
     * @param Type $type field type
     *
     * @return string
     */
    private function getMethodName(string $field, Type $type): string
    {
        $isBool = $type->isBool();
        if ($isBool && (strncmp($field, 'is_', 3) === 0)) {
            $field = substr($field, 3);
        }
        $key = ucfirst(str_replace('_', '', ucwords($field, '_')));
        return $isBool ? "is$key" : "get$key";
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
        $result = '';
        foreach ($loadedData as $className => $fields) {
            $result .= (($result !== '') ? "\n" : '') . "/**\n";
            /** @var array $fields */
            foreach ($fields as $field => $type) {
                $methodName = $this->getMethodName($field, $type);
                $result .= " * @method $type $methodName()\n";
            }
            $result .= " */\nclass {$className}DataContainer\n{\n}\n";
        }
        return $result;
    }
}
