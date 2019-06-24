<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataProcessor;

use Raptor\TestUtils\Exceptions\DataParseException;

/**
 * Interface of the data processor.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
interface DataProcessor
{
    /**
     * Processes string with data and returns array with processed data.
     *
     * @param string $data
     *
     * @return array
     *
     * @throws DataParseException
     */
    public function process(string $data): array;
}
