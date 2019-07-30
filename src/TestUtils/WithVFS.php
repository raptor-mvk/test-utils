<?php
declare(strict_types=1);

namespace Raptor\TestUtils;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Raptor\TestUtils\Exceptions\VFSNotInitializedException;

/**
 * Trait that provides adapted interface for _mikey179/vfsstream_.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 * @author Igor Vodka
 *
 * @copyright 2019, raptor_MVK
 */
trait WithVFS
{
    /** @var vfsStreamDirectory $root virtual file system */
    private $root;

    /**
     * Setups virtual file system
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ static method vfsStream::setup
     */
    protected function setupVFS(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * Adds file with given permissions and content to virtual file system.
     *
     * @param string $filename
     * @param int|null $permissions
     * @param string|null $content
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method vfsStream::newFile
     */
    protected function addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null): void
    {
        $this->assertVfsHasBeenSetUp();
        $file = vfsStream::newFile($filename, $permissions);
        if ($content !== null) {
            $file->withContent($content);
        }
        $this->root->addChild($file);
    }

    /**
     * Adds directory with given permissions to virtual file system.
     *
     * @param string $dirname
     * @param int|null $permissions
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method vfsStream::newDirectory
     */
    protected function addDirectoryToVFS(string $dirname, ?int $permissions = null): void
    {
        $this->assertVfsHasBeenSetUp();
        $directory = vfsStream::newDirectory($dirname, $permissions);
        $this->root->addChild($directory);
    }

    /**
     * Adds directory structure to virtual file system.
     *
     * @param array $structure directory structure presented as directory tree; the leaves are files, where element key
     *                         is filename and element value is file contents
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ factory method vfsStream::create
     */
    protected function addStructureToVFS(array $structure): void
    {
        $this->assertVfsHasBeenSetUp();
        vfsStream::create($structure);
    }

    /**
     * Returns full path in virtual file system by partial path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getFullPath(string $path): string
    {
        $this->assertVfsHasBeenSetUp();
        return "{$this->root->url()}/$path";
    }

    /**
     * Asserts that setupVFS has been called, and throws exception otherwise.
     */
    private function assertVfsHasBeenSetUp(): void
    {
        if ($this->root === null) {
            throw new VFSNotInitializedException('Method setupVFS should be used.');
        }
    }
}
