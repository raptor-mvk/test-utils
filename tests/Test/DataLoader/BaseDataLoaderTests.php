<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use PHPUnit\Framework\TestCase;
use Raptor\Test\DataLoader\BaseDataLoader;
use Raptor\Test\Exceptions\DataFileNotFoundException;

/**
 * Класс с тестами для базовой реализации загрузчика данных `BaseDataLoader`.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class BaseDataLoaderTests extends TestCase
{
    /**
     * Проверяет, что метод _load_ бросает исключение _DataFileNotFoundException_, если файл с данными не найден.
     */
    public function testLoadThrowsDataFileNotFoundForNonExistingFile(): void
    {
        $filename = 'some_file';
        $this->expectException(DataFileNotFoundException::class);
        $this->expectExceptionMessage("Не найден файл с данными {$filename}");
        $dataLoader = new BaseDataLoader('someClass', $filename);
        $dataLoader->load();
    }
}