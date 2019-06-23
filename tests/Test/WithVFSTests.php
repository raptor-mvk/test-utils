<?php
declare(strict_types=1);

namespace RaptorTests\Test;

use PHPUnit\Framework\TestCase;
use Raptor\Test\Exceptions\VFSNotInitializedException;
use Raptor\Test\ExtraAssertions;
use Raptor\Test\WithVFS;

/**
 * Класс с тестами для трейта _WithVFS_.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class WithVFSTests extends TestCase
{
    use WithVFS, ExtraAssertions;

    /**
     * Проверяет, что метод _getFullPath_ выбрасывает исключение _VFSNotInitializedException_, если предварительно не
     * вызван метод _setupVFS_.
     */
    public function testGetFullPathThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionMessageRegExp('/^Не вызван метод setupVFS$/');

        $this->getFullPath('some_file.json');
    }

    /**
     * Проверяет, что метод _getEscapedFullPath_ выбрасывает исключение _VFSNotInitializedException_, если
     * предварительно не вызван метод _setupVFS_.
     */
    public function testGetEscapedFullPathThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionMessageRegExp('/^Не вызван метод setupVFS$/');

        $this->getEscapedFullPath('other_file.json');
    }

    /**
     * Проверяет, что метод _getEscapedFullPath_ возвращает путь, не содержащий не экранированных символов /.
     */
    public function testGetEscapedFullPathReturnsPathWithoutNotEscapedSlashes(): void
    {
        $this->setupVFS();

        $escapedPath = $this->getEscapedFullPath('some/path/file.txt');

        static::assertNotRegExp('/[^\\\\]\//', $escapedPath);
    }

    /**
     * Проверяет, что метод _addFileToVFS_ выбрасывает исключение _VFSNotInitializedException_, если предварительно не
     * вызван метод _setupVFS_.
     */
    public function testAddFileToVFSThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionMessageRegExp('/^Не вызван метод setupVFS$/');

        $this->addFileToVFS('any_file.txt');
    }

    /**
     * Проверяет, что метод _addFileToVFS_ добавляет файл.
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
     * Проверяет, что метод _addFileToVFS_ добавляет файл с содержимым.
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
     * Проверяет, что метод _addFileToVFS_ добавляет файл с правами доступа.
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
     * Проверяет, что метод _addDirectoryToVFS_ выбрасывает исключение _VFSNotInitializedException_, если предварительно
     * не вызван метод _setupVFS_.
     */
    public function testAddDirectoryToVFSThrowsVFSNotInitializedWithoutSetupVFS(): void
    {
        $this->expectException(VFSNotInitializedException::class);
        $this->expectExceptionMessageRegExp('/^Не вызван метод setupVFS$/');

        $this->addDirectoryToVFS('any_directory');
    }

    /**
     * Проверяет, что метод _addDirectoryToVFS_ добавляет директорию.
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
     * Проверяет, что метод _addDirectoryToVFS_ добавляет директорию с правами.
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
     * Проверяет, что метод _addStructure_ добавляет структуру директорий в файловую систему.
     *
     * @param string    $path      путь к элементу
     * @param bool      $isFile    _true_, если элемент должен быть файлом, _false_, если директорией
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
        $message = "$path должен быть" . ($isFile ? 'файлом' : 'директорией');

        static::assertTrue($actualResult, $message);
    }

    /**
     * Предоставляет данные для проверки метода _addStructure_ в формате [ path, is_file].
     *
     * @return array    данные для проверки метода _addStructure_
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
            'file four' => ['file_four.dat', true]
        ];
    }

    /**
     * Готовит структуру директорий для добавления в виртуальную файловую систему.
     *
     * @return array    структура директорий
     */
    private function prepareStructure(): array
    {
        return [
            'first' => [
                'empty_dir' => [],
                'file_one.json' => 'content#1',
                'file_two.json' => 'content#2'
            ],
            'second' => [
                'internal' => [
                    'file_three.txt' => 'no_value'
                ]
            ],
            'file_four.dat' => 'a35dg5'
        ];
    }

    /**
     * Проверяет, что метод _addStructure_ добавляет содержимое в файлы из структуры директорий.
     *
     * @param string    $path       путь к элементу
     * @param string    $content    содержимое файла
     *
     * @dataProvider addStructureContentDataProvider
     */
    public function testAddStructureToVFSPutsContentToFilesFromStructure(string $path, string $content): void
    {
        $structure = $this->prepareStructure();
        $this->setupVFS();

        $this->addStructureToVFS($structure);

        $fullFilename = $this->getFullPath($path);

        static::assertStringIsSameAsFile($fullFilename, $content);
    }

    /**
     * Предоставляет данные для проверки содержимого файлов, добавленных методом _addStructure_ в формате [ path,
     * content ].
     *
     * @return array    данные для проверки метода _addStructure_
     */
    public function addStructureContentDataProvider(): array
    {
        return [
            'file one content' => ['first/file_one.json', 'content#1'],
            'file two content' => ['first/file_two.json', 'content#2'],
            'file three content' => ['second/internal/file_three.txt', 'no_value'],
            'file four content' => ['file_four.dat', 'a35dg5']
        ];
    }
}
