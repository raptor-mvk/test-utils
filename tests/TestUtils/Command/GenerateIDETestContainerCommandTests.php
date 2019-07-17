<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils\Command;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\Command\GenerateIDETestContainerCommand;
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

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
    public function setUp(): void
    {
        $this->setupVFS();
    }

    /**
     * Checks that command generates correct file.
     */
    public function testCommandGeneratesCorrectFile(): void
    {
        $fullPath = $this->prepareVFSDirectoryStructure('json');
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);

        static::assertFileEquals(__DIR__ . '/reference/test.txt', "$expectedPath/_ide_test_container.php");
    }

    /**
     * Prepares directory structure in virtual file system and returns path to it outside virtual file system.
     *
     * @param string $rootDir root directory name
     *
     * @return string
     */
    private function prepareVFSDirectoryStructure(string $rootDir): string
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
                'file_one.json' => json_encode($fileOneContent),
                'inside' => ['file_two.json' => json_encode($fileTwoContent)]
            ]
        ];
        $this->addStructureToVFS($structure);
        return $this->getFullPath($rootDir);
    }

    /**
     * Checks that command outputs correct message when file is generated without errors.
     */
    public function testCommandOutputsCorrectMessageWithoutErrors(): void
    {
        $fullPath = $this->prepareVFSDirectoryStructure('other_dir');
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);
        $output = $commandTester->getDisplay();

        static::assertSame("File _id_test_container.php was successfully generated.\n", $output);
    }

    /**
     * Checks that command returns exit code OK when file is generated without errors.
     */
    public function testCommandReturns0WithoutErrors(): void
    {
        $fullPath = $this->prepareVFSDirectoryStructure('other_dir');
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);
        $result = $commandTester->getStatusCode();

        static::assertSame(GenerateIDETestContainerCommand::OK, $result);
    }

    /**
     * Checks that command outputs correct message when file could not be generated.
     */
    public function testCommandOutputsCorrectMessageWhenCouldNotWriteToFile(): void
    {
        $fullPath = $this->prepareVFSDirectoryStructure('any_dir');
        $this->addFileToVFS('_ide_test_container.php', 000);
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);
        $output = $commandTester->getDisplay();

        static::assertSame("Could not write to the file _id_test_container.php.\n", $output);
    }

    /**
     * Checks that command returns exit code ERROR when file could not be generated.
     */
    public function testCommandReturns1WhenCouldNotWriteToFile(): void
    {
        $fullPath = $this->prepareVFSDirectoryStructure('any_dir');
        $this->addFileToVFS('_ide_test_container.php', 000);
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['path' => $fullPath]);
        $result = $commandTester->getStatusCode();

        static::assertSame(GenerateIDETestContainerCommand::ERROR, $result);
    }

    /**
     * Checks that command outputs correct message when file is generated with errors.
     */
    public function testCommandOutputsCorrectMessageWithErrors(): void
    {
        $dirname = 'dir_with_errors';
        $fullPath = $this->prepareVFSDirectoryStructureWithErrors($dirname);
        $expectedPath = $this->getFullPath('');
        $command = new GenerateIDETestContainerCommand($expectedPath);
        $commandTester = new CommandTester($command);
        $fileOne = $this->getFullPath("$dirname/file_one.json");
        $fileTwo = $this->getFullPath("$dirname/inside/file_two.json");
        $expectedMessage = "$fileOne: JSON parse error.\n$fileTwo: Expected array, object found at level root.\n" .
            "File _id_test_container.php was successfully generated.\n";

        $commandTester->execute(['path' => $fullPath]);
        $output = $commandTester->getDisplay();

        static::assertSame($expectedMessage, $output);
    }

    /**
     * Prepares directory structure in virtual file system with errors and returns path to it outside virtual file
     * system.
     *
     * @param string $rootDir root directory name
     *
     * @return string
     */
    private function prepareVFSDirectoryStructureWithErrors(string $rootDir): string
    {
        $structure = [
            $rootDir => [
                'file_one.json' => '',
                'inside' => ['file_two.json' => '{"some_field":"some_value"}']
            ]
        ];
        $this->addStructureToVFS($structure);
        return $this->getFullPath($rootDir);
    }
}
