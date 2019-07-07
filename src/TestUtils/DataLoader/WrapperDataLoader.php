<?php
declare(strict_types=1);

namespace Raptor\TestUtils\DataLoader;

use Raptor\TestUtils\DataProcessor\WrapperDataProcessor;

/**
 * Data loader that wraps loaded data items into TestDataContainer instances.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class WrapperDataLoader extends ProcessingDataLoader
{
    public function __construct()
    {
        parent::__construct(new WrapperDataProcessor());
    }
}
