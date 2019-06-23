<?php
declare(strict_types=1);

namespace Raptor\Test\TestDataContainerGenerator;

use Raptor\Test\DataLoader\DirectoryDataLoader;
use Raptor\Test\DataProcessor\TestContainerGeneratorDataProcessor;
use Raptor\Test\Exceptions\DataDirectoryNotFoundException;

/**
 * Генератор вспомогательного файла для IDE для использования контейнеров с тестовыми данными.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class TestDataContainerGenerator
{
    /** @var DirectoryDataLoader    $directoryDataLoader    загрузчик данных из всех файлов (по маске) в директории */
    private $directoryDataLoader;

    /**
     * Конструктор генератора вспомогательного файла для IDE.
     *
     * @param DirectoryDataLoader    $directoryDataLoader    загрузчик данных из всех файлов (по маске) в директории
     */
    public function __construct(DirectoryDataLoader $directoryDataLoader)
    {
        $this->directoryDataLoader = $directoryDataLoader;
    }

    /**
     * Возвращает наименование метода-геттера по наименованию поля и его типу (тип нужен для учёта логических геттеров).
     *
     * @param string    $field    наименование поля
     * @param string    $type     тип поля
     *
     * @return string    наименование метода-геттера
     */
    private function getMethodName(string $field, string $type): string
    {
        $isBool = $type === TestContainerGeneratorDataProcessor::BOOL_TYPE;
        if ($isBool && (strncmp($field, 'is_', 3) === 0)) {
            $field = substr($field, 3);
        }
        $key = ucfirst(str_replace('_', '', ucwords($field, '_')));
        return $isBool ? "is$key" : "get$key";
    }

    /**
     * Возвращает содержимое вспомогательного файла для IDE, сгенерированное с использованием данных из всех JSON-файлов
     * в директории, включая все вложенные директории.
     *
     * @param string    $path              обрабатываемый путь
     *
     * @return string    сгенерированное содержимое вспомогательного файла
     *
     * @throws DataDirectoryNotFoundException    не найдена директория с данными
     */
    public function generate(string $path): string
    {
        $loadedData = $this->directoryDataLoader->load($path, '/^.*\.json$/');
        $result = '';
        foreach ($loadedData as $className => $fields) {
            $result .= (($result !== '') ? "\n" : '') . "/**\n";
            /** @var array $fields */
            foreach ($fields as $field => $type) {
                $methodName = $this->getMethodName($field, $type);
                $result .= " * @method $type $methodName()\n";
            }
            $result .= " */\nclass {$className}DataContainer\n{\n}\n";
        }
        return $result;
    }
}