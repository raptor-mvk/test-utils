<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use function get_class;
use Raptor\TestUtils\DataProcessor\DataProcessor;
use Raptor\TestUtils\Exceptions\DataFileNotFoundException;

/**
 * Loads data and processes it with injected DataProcessor.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class DataLoader
{
    /** @var DataProcessor $dataProcessor */
    private $dataProcessor;

    /**
     * @param DataProcessor $dataProcessor
     */
    public function __construct(DataProcessor $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * Loads data, processes it and returns an array with processed data.
     *
     * @param string $filename path to the data file
     *
     * @return array
     *
     * @throws DataFileNotFoundException
     */
    public function load(string $filename): array
    {
        if (!is_readable($filename) || !is_file($filename)) {
            throw new DataFileNotFoundException("Data file $filename was not found.");
        }
        $data = file_get_contents($filename);
        return $this->dataProcessor->process($data);
    }

    /**
     * Returns classname of data processor. Used for testing purposes.
     *
     * @return string
     */
    public function getDataProcessorClass(): string
    {
        return get_class($this->dataProcessor);
    }
}
