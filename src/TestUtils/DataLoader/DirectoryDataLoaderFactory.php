<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

/**
 * Factory for directory data loaders.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class DirectoryDataLoaderFactory
{
    /** @var DataLoaderFactory $dataLoaderFactory */
    private $dataLoaderFactory;

    /**
     * @param DataLoaderFactory $dataLoaderFactory
     */
    public function __construct(DataLoaderFactory $dataLoaderFactory)
    {
        $this->dataLoaderFactory = $dataLoaderFactory;
    }

    /**
     * Creates directory data loader, that defines types of data fields in each file. Used to generate service file for
     * IDE.
     *
     * @return DirectoryDataLoader
     */
    public function createTestContainerGeneratorDataLoader(): DirectoryDataLoader
    {
        $dataLoader = $this->dataLoaderFactory->createTestContainerGeneratorDataLoader();
        return new DirectoryDataLoader($dataLoader);
    }
}
