<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DataLoaderFactory;
use Raptor\TestUtils\DataLoader\DirectoryDataLoaderFactory;
use Raptor\TestUtils\DataProcessor\TestContainerGeneratorDataProcessor;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DirectoryDataLoaderFactoryTests extends TestCase
{
    /**
     * Checks that factory method _createTestContainerGeneratorDataLoader_ returns DirectoryDataLoader with
     * TestContainerGeneratorDataProcessor as data processor.
     */
    public function testCreateTestContainerGeneratorDataLoaderUsesCorrectDataProcessor(): void
    {
        $dataLoaderFactory = new DataLoaderFactory();
        $directoryDataLoaderFactory = new DirectoryDataLoaderFactory($dataLoaderFactory);
        $directoryDataLoader = $directoryDataLoaderFactory->createTestContainerGeneratorDataLoader();

        $actualDataProcessorClass = $directoryDataLoader->getDataProcessorClass();

        static::assertSame(TestContainerGeneratorDataProcessor::class, $actualDataProcessorClass);
    }
}
