<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\Command;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\Command\GenerateIDETestContainersCommand;
use Raptor\TestUtils\DataLoader\DataLoaderFactory;
use Raptor\TestUtils\DataLoader\DirectoryDataLoaderFactory;
use Raptor\TestUtils\Generator\TestDataContainerGenerator;
use Raptor\TestUtils\WithVFS;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class GenerateIDETestContainerCommandTests extends TestCase
{
    use WithVFS;

    public function testCommandGeneratesCorrectFile(): void
    {
        $dirname = 'json';
        $this->setupVFS();
        $this->prepareVFSDirectoryStructure($dirname);
        $dataLoaderFactory = new DataLoaderFactory();
        $directoryDataLoaderFactory = new DirectoryDataLoaderFactory($dataLoaderFactory);
        $directoryDataLoader = $directoryDataLoaderFactory->createTestContainerGeneratorDataLoader();
        $generator = new TestDataContainerGenerator($directoryDataLoader);
        $fullPath = $this->getFullPath($dirname);
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainersCommand($generator, $expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);

        static::assertFileEquals(__DIR__ . '/reference/test.txt', "$expectedPath/_ide_test_container.php");
    }

    /**
     * Prepares directory structure in virtual file system.
     *
     * @param string $rootDir root directory name
     */
    private function prepareVFSDirectoryStructure(string $rootDir): void
    {
        $fileOneContent = [
            ['_name' => 'AAA', 'int' => 3, 'float' => 3.5, 'bool' => true, 'string' => 'bbb', 'array' => [1, 2, 3]],
            ['_name' => 'BBB', 'int' => 5, 'float' => 8.3, 'bool' => false, 'string' => 'ccc', 'array' => [4, 5, 6]]
        ];
        $fileTwoContent = [
            ['_name' => 'AAA', 'int_to_float' => 13, 'float_to_int' => 22.2, 'is_bool' => true, 'int_to_string' => 35],
            ['_name' => 'BBB', 'int_to_float' => 7.4, 'float_to_int' => 11, 'is_bool' => false, 'int_to_string' => 'ab']
        ];
        $structure = [
            $rootDir => [
                'file_one.json' => json_encode($fileOneContent, JSON_UNESCAPED_UNICODE),
                'inside' => ['file_two.json' => json_encode($fileTwoContent, JSON_UNESCAPED_UNICODE)]
            ]
        ];
        $this->addStructureToVFS($structure);
    }
}
