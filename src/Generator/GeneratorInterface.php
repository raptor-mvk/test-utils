<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\Generator;

use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;

/**
 * Interface for file generator.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface GeneratorInterface
{
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
    public function generate(string $path): string;

    /**
     * Returns array of errors that occurred during the last generation.
     *
     * @return array [ filename => errorMessage, ... ]
     */
    public function getLastErrors(): array;
}
