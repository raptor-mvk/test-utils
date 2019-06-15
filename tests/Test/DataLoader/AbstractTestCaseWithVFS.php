<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Абстрактный базовый класс для тестов, использующих виртуальную файловую систему.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
abstract class AbstractTestCaseWithVFS extends TestCase
{
    /** @var vfsStreamDirectory    $root    виртуальная файловая система */
    private $root;

    /**
     * Подготовка окружения.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup();
        $this->prepareVirtualFileSystem();
    }

    /**
     * Готовит виртуальную файловую систему.
     */
    abstract protected function prepareVirtualFileSystem(): void;

    /**
     * Добавляет файл в виртуальную файловую систему.
     *
     * @param string         $filename       имя добавляемого файла
     * @param int|null       $permissions    права доступа к файлу
     * @param string|null    $content        содержимое добавляемого файла
     */
    protected function addFileToVFS(string $filename, ?int $permissions = null, ?string $content = null): void
    {
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
     */
    protected function addDirectoryToVFS(string $dirname, ?int $permissions = null): void
    {
        $directory = vfsStream::newDirectory($dirname, $permissions);
        $this->root->addChild($directory);
    }

    /**
     * Добавляет структуру директорий в виртуальную файловую систему.
     *
     * @param array    $structure    структура директорий в формате дерева директорий, листьями являются файлы, где
     * ключ элемента массива – имя файла, а значение - содержимое файла
     */
    protected function addStructure(array $structure): void
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
        return str_replace('/', '\/', "{$this->root->url()}/$path");
    }
}
