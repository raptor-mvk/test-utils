<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\Exceptions\DataFileNotFoundException;

/**
 * Interface for processed data loader.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DataLoaderInterface
{
    /**
     * Loads data, processes it and returns an array with processed data.
     *
     * @param string $filename path to the data file
     *
     * @return array
     *
     * @throws DataFileNotFoundException
     */
    public function load(string $filename): array;
}
