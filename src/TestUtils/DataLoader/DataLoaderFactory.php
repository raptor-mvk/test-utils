<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TestContainerWrapperDataProcessor;

/**
 * Factory for data loaders.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class DataLoaderFactory
{
    /**
     * Creates data loader for test data wrapped into test data containers.
     *
     * @return DataLoader
     */
    public function createTestContainerWrapperDataLoader(): DataLoader
    {
        $dataProcessor = new TestContainerWrapperDataProcessor();
        return new DataLoader($dataProcessor);
    }

    /**
     * Creates data loader, that defines types of data fields. Used to generate service file for IDE.
     *
     * @return DataLoader
     */
    public function createTestContainerGeneratorDataLoader(): DataLoader
    {
        $dataProcessor = new TestContainerGeneratorDataProcessor();
        return new DataLoader($dataProcessor);
    }
}
