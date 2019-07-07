<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\DataProcessor\GeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;
use Raptor\TestUtils\DataProcessor\WrapperDataProcessor;

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
        $dataProcessor = new WrapperDataProcessor();
        return new ProcessedDataLoader($dataProcessor);
    }

    /**
     * Creates data loader, that defines types of data fields. Used to generate service file for IDE.
     *
     * @return DataLoader
     */
    public function createTestContainerGeneratorDataLoader(): DataLoader
    {
        $dataProcessor = new GeneratorDataProcessor(new GetTypeTypeFactory());
        return new ProcessedDataLoader($dataProcessor);
    }
}
