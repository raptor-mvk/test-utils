<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\DataLoader\DataLoaderFactory;
use Raptor\TestUtils\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TestContainerWrapperDataProcessor;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class DataLoaderFactoryTests extends TestCase
{
    /**
     * Checks that factory method _createTestContainerWrapperDataLoader_ returns DataLoader with
     * TestContainerWrapperDataProcessor as data processor.
     */
    public function testCreateTestContainerWrapperDataLoaderUsesCorrectDataProcessor(): void
    {
        $dataLoaderFactory = new DataLoaderFactory();
        $dataLoader = $dataLoaderFactory->createTestContainerWrapperDataLoader();

        $actualDataProcessorClass = $dataLoader->getDataProcessorClass();

        static::assertSame(TestContainerWrapperDataProcessor::class, $actualDataProcessorClass);
    }

    /**
     * Checks that factory method _createTestContainerGeneratorDataLoader_ returns DataLoader with
     * TestContainerGeneratorDataProcessor as data processor.
     */
    public function testCreateTestContainerGeneratorDataLoaderUsesCorrectDataProcessor(): void
    {
        $dataLoaderFactory = new DataLoaderFactory();
        $dataLoader = $dataLoaderFactory->createTestContainerGeneratorDataLoader();

        $actualDataProcessorClass = $dataLoader->getDataProcessorClass();

        static::assertSame(TestContainerGeneratorDataProcessor::class, $actualDataProcessorClass);
    }
}
