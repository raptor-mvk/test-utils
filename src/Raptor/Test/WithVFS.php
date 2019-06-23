<?php
declare(strict_types=1);

namespace Raptor\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Raptor\Test\Exceptions\VFSNotInitializedException;

/**
 * Трейт, представляющий собой адаптер для работы с _mikey179/vfsstream_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
trait WithVFS
{
    /** @var vfsStreamDirectory $root виртуальная файловая система */
    private $root;

    /**
     * Подготовка виртуальной файловой системы.
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ статический интерфейс vfsStream
     */
    protected function setupVFS(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * Добавляет файл в виртуальную файловую систему.
     *
     * @param string         $filename       имя добавляемого файла
     * @param int|null       $permissions    права доступа к файлу
     * @param string|null    $content        содержимое добавляемого файла
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод newFile
     */
    protected function addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null): void
    {
        if ($this->root === null) {
            throw new VFSNotInitializedException('Не вызван метод setupVFS');
        }
        $file = vfsStream::newFile($filename, $permissions);
        if ($content !== null) {
            $file->withContent($content);
        }
        $this->root->addChild($file);
    }

    /**
     * Добавляет директорию в виртуальную файловую систему.
     *
     * @param string      $dirname        имя добавляемой директории
     * @param int|null    $permissions    права доступа к директории
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ фабричный метод newDirectory
     */
    protected function addDirectoryToVFS(string $dirname, ?int $permissions = null): void
    {
        if ($this->root === null) {
            throw new VFSNotInitializedException('Не вызван метод setupVFS');
        }
        $directory = vfsStream::newDirectory($dirname, $permissions);
        $this->root->addChild($directory);
    }

    /**
     * Добавляет структуру директорий в виртуальную файловую систему.
     *
     * @param array    $structure    структура директорий в формате дерева директорий, листьями являются файлы, где
     *                               ключ элемента массива – имя файла, а значение - содержимое файла
     *
     * @SuppressWarnings(PHPMD.StaticAccess) __approved__ статический метод create
     */
    protected function addStructureToVFS(array $structure): void
    {
        vfsStream::create($structure);
    }

    /**
     * Возвращает полный путь в виртуальной файловой системе.
     *
     * @param string    $path    путь
     *
     * @return string    полный путь в виртуальной файловой системе
     */
    protected function getFullPath(string $path): string
    {
        if ($this->root === null) {
            throw new VFSNotInitializedException('Не вызван метод setupVFS');
        }
        return "{$this->root->url()}/$path";
    }

    /**
     * Возвращает полный путь в виртуальной файловой системе с экранированием для регулярных выражений.
     *
     * @param string    $path    путь
     *
     * @return string    полный путь с экранированием для регулярных выражений в виртуальной файловой системе
     */
    protected function getEscapedFullPath(string $path): string
    {
        return str_replace('/', '\/', $this->getFullPath($path));
    }

}