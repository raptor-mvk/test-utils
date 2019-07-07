<?php
declare(strict_types=1);

namespace Raptor\TestUtils;

use Raptor\TestUtils\DataLoader\DataLoader;
use Raptor\TestUtils\DataLoader\DataLoaderFactory;
use Raptor\TestUtils\Exceptions\DataFileNotFoundException;

/**
 * Trait that provides method for easy usage of DataLoader in data providers.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait WithDataLoader
{
    /** @var DataLoader $dataLoader lazily initialized data loader */
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
        if ($this->dataLoader === null) {
            $dataLoaderFactory = new DataLoaderFactory();
            $this->dataLoader = $dataLoaderFactory->createTestContainerWrapperDataLoader();
        }
        return $this->dataLoader->load($filename);
    }
}
