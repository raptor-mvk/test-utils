<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;

/**
 * Interface for data loader for all files by regexp in the provided folder.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DirectoryDataLoader
{
    /**
     * Performs recursive search by regexp in the provided folder. Loads data from all found files. Processed data from
     * each file is returned in an array element with key, which is obtained by converting the filename without path and
     * extension into CamelCase.
     *
     * @param string $path
     * @param string $filenameRegExp
     *
     * @return array
     *
     * @throws DataDirectoryNotFoundException
     */
    public function load(string $path, string $filenameRegExp): array;

    /**
     * Returns array of errors that occurred during the last data load. Filenames are array keys.
     *
     * @return array
     */
    public function getLastErrors(): array;
}
