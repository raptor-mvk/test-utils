<?php
declare(strict_types=1);

namespace Raptor\Test\DataLoader;

/**
 * Фабрика загрузчиков данных из всех файлов (по маске) в директории.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DirectoryDataLoaderFactory
{
    /**
     * Создаёт загрузчик данных из всех файлов (по маске) в директории для генерации вспомогательного файла для IDE.
     *
     * @return DirectoryDataLoader    загрузчик данных из всех файлов (по маске) в директории
     */
    public static function createTestContainerGeneratorDataLoader(): DirectoryDataLoader
    {
        $dataLoader = DataLoaderFactory::createTestContainerGeneratorDataLoader();
        return new BaseDirectoryDataLoader($dataLoader);
    }
}
