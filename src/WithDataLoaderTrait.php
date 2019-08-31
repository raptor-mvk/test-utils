<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils;

use Raptor\TestUtils\DataLoader\DataLoaderInterface;
use Raptor\TestUtils\DataLoader\WrapperDataLoader;
use Raptor\TestUtils\Exceptions\DataFileNotFoundException;

/**
 * Trait that provides method for easy usage of DataLoader in data providers.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait WithDataLoaderTrait
{
    /** @var DataLoaderInterface $dataLoader lazily initialized data loader */
    private $dataLoader;

    /**
     * Loads data, processes it and returns an array with processed data.
     *
     * @param string $filename path to the data file
     *
     * @return array
     *
     * @throws DataFileNotFoundException
     */
    protected function loadDataFromFile(string $filename): array
    {
        if (null === $this->dataLoader) {
            $this->dataLoader = new WrapperDataLoader();
        }

        return $this->dataLoader->load($filename);
    }
}
