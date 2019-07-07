<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\Exceptions\DataDirectoryNotFoundException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Throwable;

/**
 * Loads data from all files by regexp in the provided folder recursively.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class RecursiveDirectoryDataLoader implements DirectoryDataLoader
{
    /** @var DataLoader $dataLoader */
    private $dataLoader;

    /** @var array $lastErrors array of errors that occurred during the last data load */
    private $lastErrors;

    /** @var array $processedData current processed data */
    private $processedData;

    /**
     * @param DataLoader $dataLoader
     */
    public function __construct(DataLoader $dataLoader)
    {
        $this->dataLoader = $dataLoader;
    }

    /**
     * Performs recursive search by regexp in the provided folder. Loads data from all found files. Processed data from
     * each file is returned in an array element with key, which is obtained by converting the filename without path and
     * extension into CamelCase.
     *
     * @param string $path
     * @param string $filenameRegExp
     *
     * @return array
     *
     * @throws DataDirectoryNotFoundException
     */
    public function load(string $path, string $filenameRegExp): array
    {
        $this->lastErrors = [];
        $this->processedData = [];
        if (!is_readable($path) || !is_dir($path)) {
            throw new DataDirectoryNotFoundException("Root folder $path was not found.");
        }
        $directoryIteratorFlags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        $directoryIterator = new RecursiveDirectoryIterator($path, $directoryIteratorFlags);
        $mode = RecursiveIteratorIterator::LEAVES_ONLY;
        $recursiveIteratorFlags = RecursiveIteratorIterator::CATCH_GET_CHILD;
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, $mode, $recursiveIteratorFlags);
        $iterator = new RegexIterator($recursiveIterator, $filenameRegExp);
        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $this->processFile($fileInfo->getPath(), $fileInfo->getFilename(), $fileInfo->getExtension());
        }
        return $this->processedData;
    }

    /**
     * Processes file by DataLoader.
     *
     * @param string $path path to file without filename
     * @param string $filename
     * @param string $extension extension of the file (used to get filename without extension)
     */
    private function processFile(string $path, string $filename, string $extension): void
    {
        $key = ucfirst(str_replace('_', '', ucwords(basename($filename, ".$extension"), '_')));
        $unixPath = str_replace('\\', '/', $path);
        $filePath = "$unixPath/$filename";
        if (isset($this->processedData[$key])) {
            $this->lastErrors[$filePath] = "Classname of test data container $key is already in use";
            return;
        }
        try {
            $this->processedData[$key] = $this->dataLoader->load($filePath);
        } catch (Throwable $exception) {
            $this->lastErrors[$filePath] = $exception->getMessage();
        }
    }

    /**
     * Returns array of errors that occurred during the last data load. Filenames are array keys.
     *
     * @return array
     */
    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }
}
