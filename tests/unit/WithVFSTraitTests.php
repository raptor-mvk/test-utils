<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\Exceptions\VFSNotInitializedException;
use Raptor\TestUtils\ExtraAssertionsTrait;
use Raptor\TestUtils\ExtraUtilsTrait;
use Raptor\TestUtils\WithVFSTrait;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class WithVFSTraitTests extends TestCase
{
    use WithVFSTrait, ExtraAssertionsTrait, ExtraUtilsTrait;

    /**
     * Checks that method _getFullPath_ throws _VFSNotInitializedException_, if method _setupVFS_ has not been called
     * previously.
     */
    public function testGetFullPathThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionExactMessage('Method setupVFS should be used.');

        $this->getFullPath('some_file.json');
    }

    /**
     * Checks that method _addFileToVFS_ throws _VFSNotInitializedException_, if method _setupVFS_ has not been called
     * previously.
     */
    public function testAddFileToVFSThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionExactMessage('Method setupVFS should be used.');

        $this->addFileToVFS('any_file.txt');
    }

    /**
     * Checks that method _addFileToVFS_ adds file to virtual file system.
     */
    public function testAddFileToVFSAddsFile(): void
    {
        $filename = 'file.txt';
        $this->setupVFS();

        $this->addFileToVFS($filename);

        $fullFilename = $this->getFullPath($filename);
        $isFile = is_file($fullFilename);

        static::assertTrue($isFile);
    }

    /**
     * Checks that method _addFileToVFS_ adds file with contents to virtual file system.
     */
    public function testAddFileToVFSAddsFileWithContent(): void
    {
        $filename = 'readable_file.txt';
        $content = 'some file content';
        $this->setupVFS();

        $this->addFileToVFS($filename, null, $content);

        $fullFilename = $this->getFullPath($filename);
        $actualContent = file_get_contents($fullFilename);

        static::assertSame($content, $actualContent);
    }

    /**
     * Checks that method _addFileToVFS_ adds file with permissions to virtual file system.
     */
    public function testAddFileToVFSAddsFileWithPermissions(): void
    {
        $filename = 'file_with_permissions.txt';
        $permissions = 0421;
        $this->setupVFS();

        $this->addFileToVFS($filename, $permissions);

        $fullFilename = $this->getFullPath($filename);
        $actualPermissions = fileperms($fullFilename) & 0x0FFF;

        static::assertSame($permissions, $actualPermissions);
    }

    /**
     * Checks that method _addDirectoryToVFS_ throws _VFSNotInitializedException_, if method _setupVFS_ has not been
     * called previously.
     */
    public function testAddDirectoryToVFSThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionExactMessage('Method setupVFS should be used.');

        $this->addDirectoryToVFS('any_directory');
    }

    /**
     * Checks that method _addDirectoryToVFS_ adds directory to virtual file system.
     */
    public function testAddDirectoryToVFSAddsFile(): void
    {
        $filename = 'dir';
        $this->setupVFS();

        $this->addDirectoryToVFS($filename);

        $fullFilename = $this->getFullPath($filename);
        $isDirectory = is_dir($fullFilename);

        static::assertTrue($isDirectory);
    }

    /**
     * Checks that method _addDirectoryToVFS_ adds directory with permissions to virtual file system.
     */
    public function testAddDirectoryToVFSAddsDirectoryWithPermissions(): void
    {
        $filename = 'dir_with_permissions';
        $permissions = 0124;
        $this->setupVFS();

        $this->addDirectoryToVFS($filename, $permissions);

        $fullFilename = $this->getFullPath($filename);
        $actualPermissions = fileperms($fullFilename) & 0x0FFF;

        static::assertSame($permissions, $actualPermissions);
    }

    /**
     * Checks that method _addStructureToVFS_ throws _VFSNotInitializedException_, if method _setupVFS_ has not been
     * called previously.
     */
    public function testAddStructureToVFSThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionExactMessage('Method setupVFS should be used.');
        $structure = ['any_folder' => []];

        $this->addStructureToVFS($structure);
    }

    /**
     * Checks that method _addStructure_ adds directory structure to virtual file system.
     *
     * @param string $path   path to tested file/directory
     * @param bool   $isFile _true_, if tested element is file, _false_ otherwise
     *
     * @dataProvider addStructureDataProvider
     */
    public function testAddStructureToVFSAddsStructure(string $path, bool $isFile): void
    {
        $structure = $this->prepareStructure();
        $this->setupVFS();

        $this->addStructureToVFS($structure);

        $fullFilename = $this->getFullPath($path);
        $actualResult = $isFile ? is_file($fullFilename) : is_dir($fullFilename);
        $message = "$path should be".($isFile ? 'file' : 'directory');

        static::assertTrue($actualResult, $message);
    }

    /**
     * Provides test data for testing method _addStructure_.
     *
     * @return array [ [ path, is_file ], ... ]
     */
    public function addStructureDataProvider(): array
    {
        return [
            'first dir' => ['first', false],
            'empty dir' => ['first/empty_dir', false],
            'file one' => ['first/file_one.json', true],
            'file two' => ['first/file_two.json', true],
            'second dir' => ['second', false],
            'internal dir' => ['second/internal', false],
            'file three' => ['second/internal/file_three.txt', true],
            'file four' => ['file_four.dat', true],
        ];
    }

    /**
     * Checks that method _addStructure_ writes contents to files from directory structure.
     *
     * @param string $path    path to tested file
     * @param string $content expected file content
     *
     * @dataProvider addStructureContentDataProvider
     */
    public function testAddStructureToVFSPutsContentToFilesFromStructure(string $path, string $content): void
    {
        $structure = $this->prepareStructure();
        $this->setupVFS();

        $this->addStructureToVFS($structure);

        $fullFilename = $this->getFullPath($path);

        static::assertStringEqualsFile($fullFilename, $content);
    }

    /**
     * Provides test data for testing contents of files that were added by method _addStructure_.
     *
     * @return array [ [ path, content ], ... ]
     */
    public function addStructureContentDataProvider(): array
    {
        return [
            'file one content' => ['first/file_one.json', 'content#1'],
            'file two content' => ['first/file_two.json', 'content#2'],
            'file three content' => ['second/internal/file_three.txt', 'no_value'],
            'file four content' => ['file_four.dat', 'a35dg5'],
        ];
    }

    /**
     * Prepares directory structure to add to virtual file system.
     *
     * @return array
     */
    private function prepareStructure(): array
    {
        return [
            'first' => [
                'empty_dir' => [],
                'file_one.json' => 'content#1',
                'file_two.json' => 'content#2',
            ],
            'second' => [
                'internal' => [
                    'file_three.txt' => 'no_value',
                ],
            ],
            'file_four.dat' => 'a35dg5',
        ];
    }
}
