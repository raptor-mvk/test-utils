<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\UnitTests;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertionsTrait;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;
use Raptor\TestUtils\WithDataLoaderTrait;
use Raptor\TestUtils\WithVFSTrait;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class WithDataLoaderTraitTests extends TestCase
{
    use ExtraAssertionsTrait, WithVFSTrait, WithDataLoaderTrait;

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
    protected function setUp(): void
    {
        $this->setupVFS();
    }

    /**
     * Checks that method _load_ returns correct data.
     */
    public function testLoadReturnsCorrectData(): void
    {
        $filename = 'some_file.json';
        $content = $this->prepareFileContent();
        $this->addFileToVFS($filename, null, $content);
        $fullFilename = $this->getFullPath($filename);
        $extractData = static function (array $container) {
            /** @var TestDataContainer[] $container */

            return $container[0]->allData();
        };
        $expectedData = $this->prepareExpectedResult();

        $actualData = $this->loadDataFromFile($fullFilename);

        $extractedActualData = array_map($extractData, $actualData);

        static::assertArraysAreSame($expectedData, $extractedActualData);
    }

    /**
     * Prepares content of input file.
     *
     * @return string
     */
    private function prepareFileContent(): string
    {
        $data = [
            [
                'param_one' => 'some_value',
                '_children' => [
                    ['_name' => 'test1'],
                    ['_name' => 'test2', 'param_two' => 'no_value'],
                    ['_name' => 'test3', 'param_two' => 'extra_value', 'param_five' => ['empty_value', 'this_value']],
                ],
            ],
        ];

        return json_encode($data);
    }

    /**
     * Prepares expected result of data loading process.
     *
     * @return array
     */
    private function prepareExpectedResult(): array
    {
        return [
            'test1' => ['param_one' => 'some_value'],
            'test2' => ['param_one' => 'some_value', 'param_two' => 'no_value'],
            'test3' => [
                'param_one' => 'some_value',
                'param_two' => 'extra_value',
                'param_five' => ['empty_value', 'this_value'],
            ],
        ];
    }
}
